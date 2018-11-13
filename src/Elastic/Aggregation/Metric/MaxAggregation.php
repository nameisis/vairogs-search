<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Metric;

/**
 * @link https://goo.gl/vCL9Ln
 */
class MaxAggregation extends StatsAggregation
{
    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'max';
    }
}
