<?php

namespace Vairogs\Utils\Search\Service;

use Elasticsearch\Client;
use Vairogs\Utils\Search\Mapping\MetadataCollector;
use Vairogs\Utils\Search\Result\Converter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Stopwatch\Stopwatch;

trait ManagerTrait
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $config;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var array
     */
    private $bulkQueries = [];

    /**
     * @var array
     */
    private $bulkParams = [];

    /**
     * @var array
     */
    private $indexSettings;

    /**
     * @var MetadataCollector
     */
    private $metadataCollector;

    /**
     * @var string
     */
    private $commitMode = 'refresh';

    /**
     * @var int
     */
    private $bulkCommitSize = 100;

    /**
     * @var int
     */
    private $bulkCount = 0;

    /**
     * @var Repository[]
     */
    private $repositories;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Stopwatch
     */
    private $stopwatch;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
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
     * @return MetadataCollector
     */
    public function getMetadataCollector(): MetadataCollector
    {
        return $this->metadataCollector;
    }

    /**
     * @return int
     */
    public function getBulkCommitSize(): int
    {
        return $this->bulkCommitSize;
    }

    /**
     * @param int $bulkCommitSize
     */
    public function setBulkCommitSize($bulkCommitSize): void
    {
        $this->bulkCommitSize = $bulkCommitSize;
    }

    /**
     * @return string
     */
    public function getCommitMode(): string
    {
        return $this->commitMode;
    }

    /**
     * @param array $params
     */
    public function setBulkParams(array $params): void
    {
        $this->bulkParams = $params;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @return Converter
     */
    public function getConverter(): Converter
    {
        return $this->converter;
    }
}
