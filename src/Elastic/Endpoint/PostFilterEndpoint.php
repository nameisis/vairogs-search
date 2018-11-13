<?php

namespace Vairogs\Utils\Search\Elastic\Endpoint;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PostFilterEndpoint extends QueryEndpoint
{
    public const NAME = 'post_filter';

    /**
     * {@inheritdoc}
     */
    public function getOrder(): int
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(NormalizerInterface $normalizer, $format = null, array $context = [])
    {
        if (!$this->getBool()) {
            return null;
        }

        return $this->getBool()->toArray();
    }
}
