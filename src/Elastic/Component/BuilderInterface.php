<?php

namespace Vairogs\Utils\Search\Elastic\Component;

interface BuilderInterface
{
    /**
     * @return array
     */
    public function toArray(): array;

    /**
     * @return string
     */
    public function getType(): string;
}
