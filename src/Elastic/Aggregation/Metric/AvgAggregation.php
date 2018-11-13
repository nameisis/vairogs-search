<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Metric;

/**
 * @link http://goo.gl/7KOIwo
 */
class AvgAggregation extends StatsAggregation
{
    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'avg';
    }
}
