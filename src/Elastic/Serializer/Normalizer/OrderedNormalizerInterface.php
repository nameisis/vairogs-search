<?php

namespace Vairogs\Utils\Search\Elastic\Serializer\Normalizer;

interface OrderedNormalizerInterface
{
    /**
     * @return int
     */
    public function getOrder(): int;
}
