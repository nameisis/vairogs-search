<?php

namespace Vairogs\Utils\Search\Result;

use ArrayAccess;

class ArrayIterator extends AbstractResultsIterator implements ArrayAccess
{
    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        return $this->documentExists($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->getDocument($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value): void
    {
        $this->documents[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        unset($this->documents[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    protected function convertDocument(array $document)
    {
        if (\array_key_exists('_source', $document)) {
            return $document['_source'];
        }

        if (\array_key_exists('fields', $document)) {
            return \array_map('\reset', $document['fields']);
        }

        return $document;
    }
}
