<?php

namespace Vairogs\Utils\Search\Elastic\Query\FullText;

/**
 * @link https://goo.gl/YHCSTr
 */
class SimpleQueryStringQuery extends QueryStringQuery
{
    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'simple_query_string';
    }
}
