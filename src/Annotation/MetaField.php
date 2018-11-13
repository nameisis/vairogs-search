<?php

namespace Vairogs\Utils\Search\Annotation;

interface MetaField
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return array
     */
    public function getSettings(): array;
}
