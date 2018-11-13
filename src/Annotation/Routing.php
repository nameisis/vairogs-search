<?php

namespace Vairogs\Utils\Search\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class Routing implements MetaField
{
    public const NAME = '_routing';

    /**
     * @var bool
     */
    public $required = false;

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettings(): array
    {
        return [
            'required' => $this->required,
        ];
    }
}
