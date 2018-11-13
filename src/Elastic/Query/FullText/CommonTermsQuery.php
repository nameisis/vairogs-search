<?php

namespace Vairogs\Utils\Search\Elastic\Query\FullText;

/**
 * @link https://goo.gl/ioczsu
 */
class CommonTermsQuery extends MatchQuery
{
    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'common';
    }
}
