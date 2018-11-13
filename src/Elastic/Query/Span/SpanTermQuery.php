<?php

namespace Vairogs\Utils\Search\Elastic\Query\Span;

use Vairogs\Utils\Search\Elastic\Query\TermLevel\TermQuery;

/**
 * @link https://goo.gl/hJKhJ6
 */
class SpanTermQuery extends TermQuery implements SpanQueryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'span_term';
    }
}
