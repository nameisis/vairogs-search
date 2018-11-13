<?php

namespace Vairogs\Utils\Search\Elastic\Query\FullText;

/**
 * @link https://goo.gl/bfLnvV
 */
class MatchPhraseQuery extends MatchQuery
{
    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'match_phrase';
    }
}
