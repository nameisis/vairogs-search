<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Pipeline;

/**
 * @link https://goo.gl/Tt2MIR
 */
class DerivativeAggregation extends AbstractPipelineAggregation
{
    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'derivative';
    }
}
