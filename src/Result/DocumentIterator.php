<?php

namespace Vairogs\Utils\Search\Result;

use Vairogs\Utils\Search\Result\Aggregation\AggregationValue;
use ReflectionException;

class DocumentIterator extends AbstractResultsIterator
{
    /**
     * {@inheritdoc}
     * @throws ReflectionException
     */
    protected function convertDocument(array $document)
    {
        return $this->getConverter()->convertToDocument($document, $this->getManager());
    }

    /**
     * @return array
     */
    public function getAggregations(): array
    {
        $aggregations = [];
        foreach (parent::getAggregations() as $key => $aggregation) {
            $aggregations[$key] = $this->getAggregation($key);
        }

        return $aggregations;
    }

    /**
     * @param string $name
     *
     * @return array|AggregationValue
     */
    public function getAggregation($name)
    {
        $aggregations = parent::getAggregations();
        if (!\array_key_exists($name, $aggregations)) {
            return null;
        }

        return new AggregationValue($aggregations[$name]);
    }

}
