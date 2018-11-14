<?php

namespace Vairogs\Utils\Search\Annotation;

interface MetaField
{
    public function getName(): string;

    public function getSettings(): array;
}
