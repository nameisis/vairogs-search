<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Pipeline;

/**
 * @link https://goo.gl/EUzda6
 */
class CumulativeSumAggregation extends AbstractPipelineAggregation
{
    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'cumulative_sum';
    }
}
