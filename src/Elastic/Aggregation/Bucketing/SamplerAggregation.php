<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Bucketing;

use Vairogs\Utils\Search\Elastic\Aggregation\AbstractAggregation;
use Vairogs\Utils\Search\Elastic\Aggregation\Type\BucketingTrait;

/**
 * @link https://goo.gl/Yowtnc
 */
class SamplerAggregation extends AbstractAggregation
{
    use BucketingTrait;

    /**
     * @param string $shardSize
     */
    private $shardSize;

    /**
     * @param string $name
     * @param string $field
     * @param int $shardSize
     */
    public function __construct($name, $field = null, $shardSize = null)
    {
        parent::__construct($name);
        $this->setField($field);
        $this->setShardSize($shardSize);
    }

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        $out = \array_filter([
            'field' => $this->getField(),
            'shard_size' => $this->getShardSize(),
        ]);

        return $out;
    }

    /**
     * @return int
     */
    public function getShardSize(): int
    {
        return $this->shardSize;
    }

    /**
     * @param int $shardSize
     */
    public function setShardSize($shardSize): void
    {
        $this->shardSize = $shardSize;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'sampler';
    }
}
