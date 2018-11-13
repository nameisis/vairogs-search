<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Bucketing;

/**
 * @link https://goo.gl/xI7zoa
 */
class SignificantTermsAggregation extends TermsAggregation
{
    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'significant_terms';
    }
}
