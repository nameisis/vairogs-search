<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Metric;

/**
 * @link https://goo.gl/3BdnUr
 */
class MinAggregation extends StatsAggregation
{
    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'min';
    }
}
