<?php

namespace Vairogs\Utils\Search\Elastic\Query\Span;

/**
 * @link https://goo.gl/wq1jSC
 */
class SpanWithinQuery extends SpanContainingQuery
{
    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'span_within';
    }
}
