<?php

namespace Vairogs\Utils\Search\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class NestedType
{
    public const NAME = 'nested';

    /**
     * @var array
     */
    public $options = [];

    /**
     * {@inheritdoc}
     */
    public function dump(array $exclude = []): array
    {
        return \array_diff_key($this->options, $exclude);
    }
}
