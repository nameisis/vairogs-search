<?php

namespace Vairogs\Utils\Search\Elastic\Endpoint;

use Vairogs\Utils\Search\Elastic\Aggregation\AbstractAggregation;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AggregationsEndpoint extends AbstractEndpoint
{
    public const NAME = 'aggregations';

    /**
     * {@inheritdoc}
     */
    public function normalize(NormalizerInterface $normalizer, $format = null, array $context = [])
    {
        $output = [];
        if (\count($this->getAll()) > 0) {
            foreach ($this->getAll() as $aggregation) {
                /** @var AbstractAggregation $aggregation */
                $output[$aggregation->getName()] = $aggregation->toArray();
            }
        }

        return $output;
    }
}
