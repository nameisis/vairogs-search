<?php

namespace Vairogs\Utils\Search\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class ParentDocument implements MetaField
{
    public const NAME = '_parent';

    /**
     * @var string
     *
     * @Required
     */
    public $class;

    public function getName(): string
    {
        return self::NAME;
    }

    public function getSettings(): array
    {
        return [
            'type' => null,
        ];
    }
}
