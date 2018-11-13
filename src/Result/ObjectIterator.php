<?php

namespace Vairogs\Utils\Search\Result;

use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\ArrayCollection;

class ObjectIterator extends AbstractLazyCollection
{
    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var array
     */
    private $alias;

    /**
     * @param Converter $converter
     * @param array $objects
     * @param array $alias
     */
    public function __construct($converter, $objects, $alias)
    {
        $this->converter = $converter;
        $this->alias = $alias;
        $this->collection = new ArrayCollection($objects);
    }

    /**
     * @inheritDoc
     */
    protected function doInitialize(): void
    {
        $this->collection = $this->collection->map(function($rawObject) {
            return $this->convertDocument($rawObject);
        });
    }

    /**
     * {@inheritdoc}
     */
    protected function convertDocument(array $document)
    {
        return $this->converter->assignArrayToObject($document, new $this->alias['namespace'](), $this->alias['aliases']);
    }
}
