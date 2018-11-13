<?php

namespace Vairogs\Utils\Search\Mapping;

interface DumperInterface
{
    /**
     * @param array $exclude
     *
     * @return array
     */
    public function dump(array $exclude = []): array;
}
