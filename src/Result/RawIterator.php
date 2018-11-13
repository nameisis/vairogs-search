<?php

namespace Vairogs\Utils\Search\Result;

class RawIterator extends AbstractResultsIterator
{
    /**
     * {@inheritdoc}
     */
    protected function convertDocument(array $document)
    {
        return $document;
    }
}
