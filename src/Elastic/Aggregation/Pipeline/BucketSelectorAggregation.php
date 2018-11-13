<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Pipeline;

/**
 * @link https://goo.gl/IQbyyM
 */
class BucketSelectorAggregation extends BucketScriptAggregation
{
    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'bucket_selector';
    }
}
