<?php

namespace Vairogs\Utils\Search\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class ObjectType
{
    public const NAME = 'object';

    /**
     * @var array
     */
    public $options = [];

    public function dump(array $exclude = []): array
    {
        return \array_diff_key($this->options, $exclude);
    }
}
