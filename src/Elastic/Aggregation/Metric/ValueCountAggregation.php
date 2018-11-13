<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Metric;

/**
 * @link https://goo.gl/iMDXtK
 */
class ValueCountAggregation extends StatsAggregation
{
    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'value_count';
    }
}
