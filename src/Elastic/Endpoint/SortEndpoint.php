<?php

namespace Vairogs\Utils\Search\Elastic\Endpoint;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SortEndpoint extends AbstractEndpoint
{
    public const NAME = 'sort';

    /**
     * {@inheritdoc}
     */
    public function normalize(NormalizerInterface $normalizer, $format = null, array $context = [])
    {
        $output = [];
        foreach ($this->getAll() as $sort) {
            $output[] = $sort->toArray();
        }

        return $output;
    }
}
