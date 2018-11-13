<?php

namespace Vairogs\Utils\Search\Elastic\InnerHit;

class ParentInnerHit extends NestedInnerHit
{
    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'parent';
    }
}
