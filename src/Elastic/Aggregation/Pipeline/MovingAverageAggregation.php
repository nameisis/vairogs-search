<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Pipeline;

/**
 * @link https://goo.gl/8gIfok
 */
class MovingAverageAggregation extends AbstractPipelineAggregation
{
    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'moving_avg';
    }
}
