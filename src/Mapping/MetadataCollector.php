<?php

namespace Vairogs\Utils\Search\Mapping;

use Doctrine\Common\Cache\CacheProvider;
use Vairogs\Utils\Search\Exception\DocumentParserException;
use Vairogs\Utils\Search\Exception\MissingDocumentAnnotationException;
use LogicException;
use ReflectionClass;
use ReflectionException;

class MetadataCollector
{
    /**
     * @var DocumentFinder
     */
    private $finder;

    /**
     * @var DocumentParser
     */
    private $parser;

    /**
     * @var CacheProvider
     */
    private $cache;

    /**
     * @var bool
     */
    private $enableCache = false;

    /**
     * @param DocumentFinder $finder
     * @param DocumentParser $parser
     * @param CacheProvider $cache
     */
    public function __construct($finder, $parser, $cache = null)
    {
        $this->finder = $finder;
        $this->parser = $parser;
        $this->cache = $cache;
    }

    /**
     * @param bool $enableCache
     */
    public function setEnableCache($enableCache): void
    {
        $this->enableCache = $enableCache;
    }

    /**
     * @param array $manager
     *
     * @return array
     * @throws ReflectionException
     */
    public function getManagerTypes($manager): array
    {
        $mapping = $this->getMappings($manager['mappings']);

        return \array_keys($mapping);
    }

    /**
     * @param string[] $bundles
     *
     * @return array
     * @throws ReflectionException
     */
    public function getMappings(array $bundles): array
    {
        $output = [];
        foreach ($bundles as $name => $bundleConfig) {
            if (!\is_array($bundleConfig)) {
                $name = $bundleConfig;
                $bundleConfig = [];
            }
            $mappings = $this->getBundleMapping($name, $bundleConfig);
            $alreadyDefinedTypes = \array_intersect_key($mappings, $output);
            if (\count($alreadyDefinedTypes)) {
                throw new LogicException(\implode(',', \array_keys($alreadyDefinedTypes)).' type(s) already defined in other document, you can use the same '.'type only once in a manager definition.');
            }
            $output = \array_merge($output, $mappings);
        }

        return $output;
    }

    /**
     * @param string $name
     * @param array $config
     *
     * @return array
     * @throws ReflectionException
     */
    public function getBundleMapping($name, array $config = []): array
    {
        if (!\is_string($name)) {
            throw new LogicException('getBundleMapping() in the Metadata collector expects a string argument only!');
        }
        $cacheName = 'vairogs.utils.search.metadata.mapping.'.\md5($name.\serialize($config));
        $this->enableCache && $mappings = $this->cache->fetch($cacheName);
        if ($mappings !== null && false !== $mappings) {
            return $mappings;
        }
        $mappings = [];
        $documentDir = $config['document_dir'] ?? $this->finder->getDocumentDir();
        if (\strpos($name, ':') !== false) {
            [$bundle, $documentClass] = \explode(':', $name);
            $documents = $this->finder->getBundleDocumentClasses($bundle);
            $documents = \in_array($documentClass, $documents, true) ? [$documentClass] : [];
        } else {
            $documents = $this->finder->getBundleDocumentClasses($name, $documentDir);
            $bundle = $name;
        }
        $bundleNamespace = $this->finder->getBundleClass($bundle);
        $bundleNamespace = \substr($bundleNamespace, 0, \strrpos($bundleNamespace, '\\'));
        if (!\count($documents)) {
            return [];
        }
        foreach ($documents as $document) {
            $documentReflection = new ReflectionClass($bundleNamespace.'\\'.\str_replace('/', '\\', $documentDir).'\\'.$document);
            try {
                $documentMapping = $this->getDocumentReflectionMapping($documentReflection);
                if (!$documentMapping) {
                    continue;
                }
            } catch (DocumentParserException $exception) {
                continue;
            }
            if (!\array_key_exists($documentMapping['type'], $mappings)) {
                $documentMapping['bundle'] = $bundle;
                $mappings = \array_merge($mappings, [$documentMapping['type'] => $documentMapping]);
            } else {
                throw new \LogicException($bundle.' has 2 same type names defined in the documents. '.'Type names must be unique!');
            }
        }
        $this->enableCache && $this->cache->save($cacheName, $mappings);

        return $mappings;
    }

    /**
     * @param ReflectionClass $reflectionClass
     *
     * @return array
     * @throws MissingDocumentAnnotationException
     * @throws ReflectionException
     */
    private function getDocumentReflectionMapping(ReflectionClass $reflectionClass): array
    {
        return $this->parser->parse($reflectionClass);
    }

    /**
     * @param string $className
     *
     * @return string
     * @throws DocumentParserException
     * @throws ReflectionException
     */
    public function getDocumentType($className): string
    {
        $mapping = $this->getMapping($className);

        return $mapping['type'];
    }

    /**
     * @param string $namespace
     *
     * @return array
     * @throws DocumentParserException
     * @throws ReflectionException
     */
    public function getMapping($namespace): array
    {
        $cacheName = 'vairogs.utils.search.metadata.document.'.\md5($namespace);
        $namespace = $this->getClassName($namespace);
        $this->enableCache && $mapping = $this->cache->fetch($cacheName);
        if ($mapping !== null && false !== $mapping) {
            return $mapping;
        }
        $mapping = $this->getDocumentReflectionMapping(new ReflectionClass($namespace));
        $this->enableCache && $this->cache->save($cacheName, $mapping);

        return $mapping;
    }

    /**
     * @param string $className
     * @param string $directory
     *
     * @return string
     */
    public function getClassName($className, $directory = null): string
    {
        return $this->finder->getNamespace($className, $directory);
    }

    /**
     * @param array $bundles
     *
     * @return array|null
     * @throws ReflectionException
     */
    public function getClientMapping(array $bundles): ?array
    {
        $typesMapping = null;
        /** @var array $typesMapping */
        $mappings = $this->getMappings($bundles);
        /** @var array $mappings */
        foreach ($mappings as $type => $mapping) {
            if (!empty($mapping['properties'])) {
                $typesMapping[$type] = \array_filter(\array_merge(['properties' => $mapping['properties']], $mapping['fields']), function($value) {
                    return (bool)$value || \is_bool($value);
                });
            }
        }

        return $typesMapping;
    }

    /**
     * @param array $bundles
     * @param array $analysisConfig
     *
     * @return array
     * @throws ReflectionException
     */
    public function getClientAnalysis(array $bundles, array $analysisConfig = []): array
    {
        $cacheName = 'vairogs.utils.search.metadata.analysis.'.\md5(\serialize($bundles));
        $this->enableCache && $typesAnalysis = $this->cache->fetch($cacheName);
        if ($typesAnalysis !== null && false !== $typesAnalysis) {
            return $typesAnalysis;
        }
        $typesAnalysis = [
            'analyzer' => [],
            'filter' => [],
            'tokenizer' => [],
            'char_filter' => [],
        ];
        $mappings = $this->getMappings($bundles);
        /** @var array $mappings */
        foreach ($mappings as $type => $metadata) {
            foreach ($metadata['analyzers'] as $analyzerName) {
                if (isset($analysisConfig['analyzer'][$analyzerName])) {
                    $analyzer = $analysisConfig['analyzer'][$analyzerName];
                    $typesAnalysis['analyzer'][$analyzerName] = $analyzer;
                    $typesAnalysis['filter'] = $this->getAnalysisNodeConfiguration('filter', $analyzer, $analysisConfig, $typesAnalysis['filter']);
                    $typesAnalysis['tokenizer'] = $this->getAnalysisNodeConfiguration('tokenizer', $analyzer, $analysisConfig, $typesAnalysis['tokenizer']);
                    $typesAnalysis['char_filter'] = $this->getAnalysisNodeConfiguration('char_filter', $analyzer, $analysisConfig, $typesAnalysis['char_filter']);
                }
            }
        }
        $this->enableCache && $this->cache->save($cacheName, $typesAnalysis);

        return $typesAnalysis;
    }

    /**
     * @param string $type
     * @param array $analyzer
     * @param array $analysisConfig
     * @param array $container
     *
     * @return array
     */
    private function getAnalysisNodeConfiguration($type, $analyzer, $analysisConfig, array $container = []): array
    {
        if (isset($analyzer[$type])) {
            if (\is_array($analyzer[$type])) {
                foreach ($analyzer[$type] as $filter) {
                    if (isset($analysisConfig[$type][$filter])) {
                        $container[$filter] = $analysisConfig[$type][$filter];
                    }
                }
            } elseif (isset($analysisConfig[$type][$analyzer[$type]])) {
                $container[$analyzer[$type]] = $analysisConfig[$type][$analyzer[$type]];
            }
        }

        return $container;
    }
}
