<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Type;

trait BucketingTrait
{
    /**
     * @return bool
     */
    protected function supportsNesting(): bool
    {
        return true;
    }
}
