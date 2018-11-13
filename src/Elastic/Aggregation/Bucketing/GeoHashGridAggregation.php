<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Bucketing;

use Vairogs\Utils\Search\Elastic\Aggregation\AbstractAggregation;
use Vairogs\Utils\Search\Elastic\Aggregation\Type\BucketingTrait;
use LogicException;

/**
 * @link https://goo.gl/Ytgt6B
 */
class GeoHashGridAggregation extends AbstractAggregation
{
    use BucketingTrait;

    /**
     * @var int
     */
    private $precision;

    /**
     * @var int
     */
    private $size;

    /**
     * @var int
     */
    private $shardSize;

    /**
     * @param string $name
     * @param string $field
     * @param int $precision
     * @param int $size
     * @param int $shardSize
     */
    public function __construct($name, $field = null, $precision = null, $size = null, $shardSize = null)
    {
        parent::__construct($name);
        $this->setField($field);
        $this->setPrecision($precision);
        $this->setSize($size);
        $this->setShardSize($shardSize);
    }

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        $data = [];
        if ($this->getField()) {
            $data['field'] = $this->getField();
        } else {
            throw new LogicException('Geo bounds aggregation must have a field set.');
        }
        if ($this->getPrecision()) {
            $data['precision'] = $this->getPrecision();
        }
        if ($this->getSize()) {
            $data['size'] = $this->getSize();
        }
        if ($this->getShardSize()) {
            $data['shard_size'] = $this->getShardSize();
        }

        return $data;
    }

    /**
     * @return int
     */
    public function getPrecision(): int
    {
        return $this->precision;
    }

    /**
     * @param int $precision
     */
    public function setPrecision($precision): void
    {
        $this->precision = $precision;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function setSize($size): void
    {
        $this->size = $size;
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
        return 'geohash_grid';
    }
}
