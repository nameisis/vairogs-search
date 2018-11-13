<?php

namespace Vairogs\Utils\Search\Elastic\Serializer;

use Vairogs\Utils\Search\Elastic\Serializer\Normalizer\OrderedNormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class OrderedSerializer extends Serializer
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        return parent::denormalize(\is_array($data) ? $this->order($data) : $data, $type, $format, $context);
    }

    /**
     * @param array $data Data to order.
     *
     * @return array
     */
    private function order(array $data): array
    {
        $filteredData = $this->filterOrderable($data);
        if (!empty($filteredData)) {
            \uasort($filteredData, function(OrderedNormalizerInterface $a, OrderedNormalizerInterface $b) {
                return $a->getOrder() > $b->getOrder();
            });

            return \array_merge($filteredData, \array_diff_key($data, $filteredData));
        }

        return $data;
    }

    /**
     * @param array $array Data to filter out.
     *
     * @return array
     */
    private function filterOrderable($array): array
    {
        return \array_filter($array, function($value) {
            return $value instanceof OrderedNormalizerInterface;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($data, $format = null, array $context = [])
    {
        return parent::normalize(\is_array($data) ? $this->order($data) : $data, $format, $context);
    }
}
