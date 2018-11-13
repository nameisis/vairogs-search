<?php

namespace Vairogs\Utils\Search\Result\Aggregation;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use LogicException;

class AggregationValue implements ArrayAccess, IteratorAggregate
{
    /**
     * @var array
     */
    private $rawData;

    /**
     * @param array $rawData
     */
    public function __construct($rawData)
    {
        $this->rawData = $rawData;
    }

    /**
     * @return array|int
     */
    public function getCount(): int
    {
        return $this->getValue('doc_count');
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function getValue($name = 'key'): array
    {
        return $this->rawData[$name] ?? null;
    }

    /**
     * @param string $path
     *
     * @return AggregationValue|null
     */
    public function find($path): ?AggregationValue
    {
        $name = \explode('.', $path, 2);
        $aggregation = $this->getAggregation($name[0]);
        if ($aggregation === null || !isset($name[1])) {
            return $aggregation;
        }

        return $aggregation->find($name[1]);
    }

    /**
     * @param string $name
     *
     * @return AggregationValue|null
     */
    public function getAggregation($name): ?AggregationValue
    {
        return new self($this->rawData[$name]) ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        return \array_key_exists($offset, $this->rawData);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->rawData[$offset] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value): void
    {
        throw new LogicException('Aggregation result can not be changed on runtime.');
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        throw new LogicException('Aggregation result can not be changed on runtime.');
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        $buckets = $this->getBuckets();
        if ($buckets === null) {
            throw new LogicException('Can not iterate over aggregation without buckets!');
        }

        return new ArrayIterator($this->getBuckets());
    }

    /**
     * @return AggregationValue[]|null
     */
    public function getBuckets(): ?array
    {
        if (!isset($this->rawData['buckets'])) {
            return null;
        }
        $buckets = [];
        foreach ($this->rawData['buckets'] as $bucket) {
            $buckets[] = new self($bucket);
        }

        return $buckets;
    }
}
