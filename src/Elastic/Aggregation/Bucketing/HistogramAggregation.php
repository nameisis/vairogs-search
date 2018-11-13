<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Bucketing;

use Vairogs\Utils\Search\Elastic\Aggregation\AbstractAggregation;
use Vairogs\Utils\Search\Elastic\Aggregation\Type\BucketingTrait;
use LogicException;

/**
 * @link https://goo.gl/bVeHkm
 */
class HistogramAggregation extends AbstractAggregation
{
    use BucketingTrait;

    public const DIRECTION_ASC = 'asc';
    public const DIRECTION_DESC = 'desc';

    /**
     * @var int
     */
    protected $interval;

    /**
     * @var int
     */
    protected $minDocCount;

    /**
     * @var array
     */
    protected $extendedBounds;

    /**
     * @var string
     */
    protected $orderMode;

    /**
     * @var string
     */
    protected $orderDirection;

    /**
     * @var bool
     */
    protected $keyed;

    /**
     * @param string $name
     * @param string $field
     * @param int $interval
     * @param int $minDocCount
     * @param string $orderMode
     * @param string $orderDirection
     * @param int $extendedBoundsMin
     * @param int $extendedBoundsMax
     * @param bool $keyed
     */
    public function __construct($name, $field = null, $interval = null, $minDocCount = null, $orderMode = null, $orderDirection = self::DIRECTION_ASC, $extendedBoundsMin = null, $extendedBoundsMax = null, $keyed = null)
    {
        parent::__construct($name);
        $this->setField($field);
        $this->setInterval($interval);
        $this->setMinDocCount($minDocCount);
        $this->setOrder($orderMode, $orderDirection);
        $this->setExtendedBounds($extendedBoundsMin, $extendedBoundsMax);
        $this->setKeyed($keyed);
    }

    /**
     * @param string $mode
     * @param string $direction
     */
    public function setOrder($mode, $direction = self::DIRECTION_ASC): void
    {
        $this->orderMode = $mode;
        $this->orderDirection = $direction;
    }

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        $out = \array_filter([
            'field' => $this->getField(),
            'interval' => $this->getInterval(),
            'min_doc_count' => $this->getMinDocCount(),
            'extended_bounds' => $this->getExtendedBounds(),
            'keyed' => $this->isKeyed(),
            'order' => $this->getOrder(),
        ], function($val) {
            return ($val || \is_numeric($val));
        });
        $this->checkRequiredParameters($out, ['field', 'interval']);

        return $out;
    }

    /**
     * @return int
     */
    public function getInterval(): int
    {
        return $this->interval;
    }

    /**
     * @param int $interval
     */
    public function setInterval($interval): void
    {
        $this->interval = $interval;
    }

    /**
     * @return int
     */
    public function getMinDocCount(): int
    {
        return $this->minDocCount;
    }

    /**
     * @param int $minDocCount
     */
    public function setMinDocCount($minDocCount): void
    {
        $this->minDocCount = $minDocCount;
    }

    /**
     * @return array
     */
    public function getExtendedBounds(): array
    {
        return $this->extendedBounds;
    }

    /**
     * @param int $min
     * @param int $max
     */
    public function setExtendedBounds($min = null, $max = null): void
    {
        $bounds = \array_filter([
            'min' => $min,
            'max' => $max,
        ], '\strlen');
        $this->extendedBounds = $bounds;
    }

    /**
     * @return bool
     */
    public function isKeyed(): bool
    {
        return $this->keyed;
    }

    /**
     * @param bool $keyed
     */
    public function setKeyed($keyed): void
    {
        $this->keyed = $keyed;
    }

    /**
     * @return array
     */
    public function getOrder(): array
    {
        if ($this->orderMode && $this->orderDirection) {
            return [$this->orderMode => $this->orderDirection];
        }

        return null;
    }

    /**
     * @param array $data
     * @param array $required
     *
     * @throws LogicException
     */
    protected function checkRequiredParameters($data, $required): void
    {
        if (\count(\array_intersect_key(\array_flip($required), $data)) !== \count($required)) {
            throw new LogicException('Histogram aggregation must have field and interval set.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'histogram';
    }
}
