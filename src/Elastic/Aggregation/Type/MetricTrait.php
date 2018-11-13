<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Type;

trait MetricTrait
{
    /**
     * @return bool
     */
    protected function supportsNesting(): bool
    {
        return false;
    }
}
