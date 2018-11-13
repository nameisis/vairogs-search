<?php

namespace Vairogs\Utils\Search\Event;

use Elasticsearch\ClientBuilder;
use Symfony\Component\EventDispatcher\Event;

class PreCreateManagerEvent extends Event
{
    /**
     * @var ClientBuilder
     */
    private $client;

    /**
     * @var array
     */
    private $indexSettings;

    /**
     * @param ClientBuilder $client
     * @param $indexSettings array
     */
    public function __construct(ClientBuilder $client, &$indexSettings)
    {
        $this->client = $client;
        $this->indexSettings = $indexSettings;
    }

    /**
     * @return ClientBuilder
     */
    public function getClient(): ClientBuilder
    {
        return $this->client;
    }

    /**
     * @param ClientBuilder $client
     */
    public function setClient(ClientBuilder $client): void
    {
        $this->client = $client;
    }

    /**
     * @return array
     */
    public function getIndexSettings(): array
    {
        return $this->indexSettings;
    }

    /**
     * @param array $indexSettings
     */
    public function setIndexSettings($indexSettings): void
    {
        $this->indexSettings = $indexSettings;
    }
}
