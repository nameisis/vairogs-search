<?php

namespace Vairogs\Utils\Search\Elastic\Query\FullText;

/**
 * @link https://goo.gl/qz7CLZ
 */
class MatchPhrasePrefixQuery extends MatchQuery
{
    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'match_phrase_prefix';
    }
}
