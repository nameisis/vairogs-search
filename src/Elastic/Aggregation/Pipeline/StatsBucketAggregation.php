<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Pipeline;

/**
 * @link https://goo.gl/apQXaQ
 */
class StatsBucketAggregation extends AbstractPipelineAggregation
{
    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'stats_bucket';
    }
}
