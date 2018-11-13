<?php

namespace Vairogs\Utils\Search\Service;

use Elasticsearch\ClientBuilder;
use Vairogs\Utils\Search\Event\Events;
use Vairogs\Utils\Search\Event\PostCreateManagerEvent;
use Vairogs\Utils\Search\Event\PreCreateManagerEvent;
use Vairogs\Utils\Search\Mapping\MetadataCollector;
use Vairogs\Utils\Search\Result\Converter;
use Psr\Log\LoggerInterface;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class ManagerFactory
{
    /**
     * @var MetadataCollector
     */
    private $metadataCollector;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var LoggerInterface
     */
    private $tracer;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Stopwatch
     */
    private $stopwatch;

    /**
     * @param MetadataCollector $metadataCollector
     * @param Converter $converter
     * @param LoggerInterface $tracer
     * @param LoggerInterface $logger
     */
    public function __construct($metadataCollector, $converter, $tracer = null, $logger = null)
    {
        $this->metadataCollector = $metadataCollector;
        $this->converter = $converter;
        $this->tracer = $tracer;
        $this->logger = $logger;
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Stopwatch $stopwatch
     */
    public function setStopwatch(Stopwatch $stopwatch): void
    {
        $this->stopwatch = $stopwatch;
    }

    /**
     * @param string $managerName
     * @param array $connection
     * @param array $analysis
     * @param array $managerConfig
     *
     * @return Manager
     * @throws ReflectionException
     */
    public function createManager($managerName, $connection, $analysis, $managerConfig): Manager
    {
        $mappings = $this->metadataCollector->getClientMapping($managerConfig['mappings']);
        $client = ClientBuilder::create();
        $client->setHosts($connection['hosts']);
        $client->setTracer($this->tracer);
        if ($this->logger && $managerConfig['logger']['enabled']) {
            $client->setLogger($this->logger);
        }
        $indexSettings = [
            'index' => $connection['index_name'],
            'body' => \array_filter([
                'settings' => \array_merge($connection['settings'], [
                    'analysis' => $this->metadataCollector->getClientAnalysis($managerConfig['mappings'], $analysis),
                ]),
                'mappings' => $mappings,
            ]),
        ];
        $this->eventDispatcher && $this->eventDispatcher->dispatch(Events::PRE_MANAGER_CREATE, new PreCreateManagerEvent($client, $indexSettings));
        $manager = new Manager($managerName, $managerConfig, $client->build(), $indexSettings, $this->metadataCollector, $this->converter);
        if ($this->stopwatch !== null) {
            $manager->setStopwatch($this->stopwatch);
        }
        $manager->setCommitMode($managerConfig['commit_mode']);
        $manager->setEventDispatcher($this->eventDispatcher);
        $manager->setCommitMode($managerConfig['commit_mode']);
        $manager->setBulkCommitSize($managerConfig['bulk_size']);
        $this->eventDispatcher && $this->eventDispatcher->dispatch(Events::POST_MANAGER_CREATE, new PostCreateManagerEvent($manager));

        return $manager;
    }
}
