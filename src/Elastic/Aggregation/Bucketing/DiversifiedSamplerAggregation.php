<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Bucketing;

/**
 * @link https://goo.gl/yzXvqD
 */
class DiversifiedSamplerAggregation extends SamplerAggregation
{
    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return 'diversified_sampler';
    }
}
