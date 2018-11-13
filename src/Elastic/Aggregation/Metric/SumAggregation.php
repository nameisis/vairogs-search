<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Metric;

/**
 * @link https://goo.gl/J8bCZz
 */
class SumAggregation extends StatsAggregation
{
    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'sum';
    }
}
