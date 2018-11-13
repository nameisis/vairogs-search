<?php

namespace Vairogs\Utils\Search\Elastic\Endpoint;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use OverflowException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class HighlightEndpoint extends AbstractEndpoint
{
    public const NAME = 'highlight';

    /**
     * @var BuilderInterface
     */
    private $highlight;

    /**
     * @var string
     */
    private $key;

    /**
     * {@inheritdoc}
     */
    public function add(BuilderInterface $builder, $key = null): string
    {
        if ($this->highlight) {
            throw new OverflowException('Only one highlight can be set');
        }
        $this->key = $key;
        $this->highlight = $builder;

        return $key;
    }

    /**
     * {@inheritdoc}
     */
    public function getAll($boolType = null): array
    {
        return [$this->key => $this->highlight];
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(NormalizerInterface $normalizer, $format = null, array $context = [])
    {
        if ($this->highlight) {
            return $this->highlight->toArray();
        }

        return null;
    }

    /**
     * @return BuilderInterface
     */
    public function getHighlight(): BuilderInterface
    {
        return $this->highlight;
    }
}
