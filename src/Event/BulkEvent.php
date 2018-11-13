<?php

namespace Vairogs\Utils\Search\Event;

use Symfony\Component\EventDispatcher\Event;

class BulkEvent extends Event
{
    /**
     * @var string
     */
    private $operation;

    /**
     * @var string|array
     */
    private $type;

    /**
     * @var array
     */
    private $query;

    /**
     * @param string $operation
     * @param string|array $type
     * @param array $query
     */
    public function __construct($operation, $type, array $query)
    {
        $this->type = $type;
        $this->query = $query;
        $this->operation = $operation;
    }

    /**
     * @return array|string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param array|string $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * @param array $query
     */
    public function setQuery($query): void
    {
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * @param string $operation
     */
    public function setOperation($operation): void
    {
        $this->operation = $operation;
    }
}
