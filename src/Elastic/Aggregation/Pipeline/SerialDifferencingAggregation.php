<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Pipeline;

/**
 * @link https://goo.gl/46ZR4v
 */
class SerialDifferencingAggregation extends AbstractPipelineAggregation
{
    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'serial_diff';
    }
}
