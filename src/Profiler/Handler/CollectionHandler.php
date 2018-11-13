<?php

namespace Vairogs\Utils\Search\Profiler\Handler;

use Monolog\Handler\AbstractProcessingHandler;

class CollectionHandler extends AbstractProcessingHandler
{
    /**
     * @var array
     */
    private $records = [];

    /**
     * @return array
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    public function clearRecords(): void
    {
        $this->records = [];
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record): void
    {
        $this->records[] = $record;
    }
}
