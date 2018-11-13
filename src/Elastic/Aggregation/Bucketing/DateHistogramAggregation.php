<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Bucketing;

use Vairogs\Utils\Search\Elastic\Aggregation\AbstractAggregation;
use Vairogs\Utils\Search\Elastic\Aggregation\Type\BucketingTrait;
use LogicException;

/**
 * @link https://goo.gl/hGCdDd
 */
class DateHistogramAggregation extends AbstractAggregation
{
    use BucketingTrait;

    /**
     * @var string
     */
    protected $interval;

    /**
     * @param string $name
     * @param string $field
     * @param string $interval
     */
    public function __construct($name, $field = null, $interval = null)
    {
        parent::__construct($name);
        $this->setField($field);
        $this->setInterval($interval);
    }

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        if (!$this->getField() || !$this->getInterval()) {
            throw new LogicException('Date histogram aggregation must have field and interval set.');
        }
        $out = [
            'field' => $this->getField(),
            'interval' => $this->getInterval(),
        ];

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
     * @param string $interval
     */
    public function setInterval($interval): void
    {
        $this->interval = $interval;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'date_histogram';
    }
}
