<?php

namespace Vairogs\Utils\Search\Elastic\Endpoint;

use Vairogs\Utils\Search\Elastic\InnerHit\NestedInnerHit;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class InnerHitsEndpoint extends AbstractEndpoint
{
    public const NAME = 'inner_hits';

    /**
     * {@inheritdoc}
     */
    public function normalize(NormalizerInterface $normalizer, $format = null, array $context = [])
    {
        $output = [];
        if (\count($this->getAll()) > 0) {
            foreach ($this->getAll() as $innerHit) {
                /** @var NestedInnerHit $innerHit */
                $output[$innerHit->getName()] = $innerHit->toArray();
            }
        }

        return $output;
    }
}
