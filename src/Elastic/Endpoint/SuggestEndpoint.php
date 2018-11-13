<?php

namespace Vairogs\Utils\Search\Elastic\Endpoint;

use Vairogs\Utils\Search\Elastic\Suggest;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SuggestEndpoint extends AbstractEndpoint
{
    public const NAME = 'suggest';

    /**
     * {@inheritdoc}
     */
    public function normalize(NormalizerInterface $normalizer, $format = null, array $context = [])
    {
        $output = [[]];
        if (\count($this->getAll()) > 0) {
            foreach ($this->getAll() as $suggest) {
                /** @var Suggest $suggest */
                $output[] = $suggest->toArray();
            }
        }

        return \array_merge(...$output);
    }
}
