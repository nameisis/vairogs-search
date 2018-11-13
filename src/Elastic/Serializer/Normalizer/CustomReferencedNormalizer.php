<?php

namespace Vairogs\Utils\Search\Elastic\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\CustomNormalizer;

class CustomReferencedNormalizer extends CustomNormalizer
{
    /**
     * @var array
     */
    private $references = [];

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $object->setReferences($this->references);
        $data = parent::normalize($object, $format, $context);
        $this->references = \array_merge($this->references, $object->getReferences());

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof AbstractNormalizable;
    }
}
