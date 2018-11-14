<?php

namespace Vairogs\Utils\Search\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class Version implements MetaField
{
    public const NAME = '_version';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getSettings(): array
    {
        return [];
    }
}
