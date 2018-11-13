<?php

namespace Vairogs\Utils\Search\Mapping;

use LogicException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use ReflectionClass;
use ReflectionException;
use RegexIterator;

class DocumentFinder
{
    /**
     * @var array
     */
    private $bundles;

    /**
     * @var string
     */
    private $documentDir;

    /**
     * @param array $bundles
     */
    public function __construct(array $bundles)
    {
        $this->documentDir = 'Document';
        $this->bundles = $bundles;
    }

    /**
     * @return string
     */
    public function getDocumentDir(): string
    {
        return $this->documentDir;
    }

    /**
     * @param string $documentDir
     */
    public function setDocumentDir($documentDir): void
    {
        $this->documentDir = $documentDir;
    }

    /**
     * @param string $namespace
     * @param string $documentsDirectory
     *
     * @return string
     */
    public function getNamespace($namespace, $documentsDirectory = null): string
    {
        if (!$documentsDirectory) {
            $documentsDirectory = $this->documentDir;
        }
        if (\strpos($namespace, ':') !== false) {
            [$bundle, $document] = \explode(':', $namespace);
            $bundle = $this->getBundleClass($bundle);
            if (\strpos($documentsDirectory, '\\') !== false) {
                $bundleSubNamespace = \substr($bundle, $start = \strpos($bundle, '\\') + 1, \strrpos($bundle, '\\') - $start + 1);
                $documentsDirectory = \str_replace($bundleSubNamespace, '', $documentsDirectory);
            }
            $namespace = \substr($bundle, 0, \strrpos($bundle, '\\')).'\\'.$documentsDirectory.'\\'.$document;
        }

        return $namespace;
    }

    /**
     * @param string $name
     *
     * @return string
     *
     * @throws LogicException
     */
    public function getBundleClass($name): string
    {
        if (\array_key_exists($name, $this->bundles)) {
            return $this->bundles[$name];
        }
        throw new LogicException(\sprintf('Bundle \'%s\' does not exist.', $name));
    }

    /**
     * @param string $bundle
     * @param string $documentsDirectory
     *
     * @return array
     * @throws ReflectionException
     */
    public function getBundleDocumentClasses($bundle, $documentsDirectory = null): array
    {
        if (!$documentsDirectory) {
            $documentsDirectory = $this->documentDir;
        }
        $bundleReflection = new ReflectionClass($this->getBundleClass($bundle));
        $documentsDirectory = \DIRECTORY_SEPARATOR.\str_replace('\\', '/', $documentsDirectory).\DIRECTORY_SEPARATOR;
        $directory = \dirname($bundleReflection->getFileName()).$documentsDirectory;
        if (!\is_dir($directory)) {
            return [];
        }
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        $files = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
        $documents = [];
        foreach ($files as $file => $v) {
            $documents[] = \str_replace(\DIRECTORY_SEPARATOR, '\\', \substr(\strstr($file, $documentsDirectory), \strlen($documentsDirectory), -4));
        }

        return $documents;
    }
}
