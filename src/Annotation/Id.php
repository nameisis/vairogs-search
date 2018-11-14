<?php

namespace Vairogs\Utils\Search\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class Id implements MetaField
{
    public const NAME = '_id';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getSettings(): array
    {
        return [];
    }
}
