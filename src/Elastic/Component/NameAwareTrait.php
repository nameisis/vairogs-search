<?php

namespace Vairogs\Utils\Search\Elastic\Component;

trait NameAwareTrait
{
    /**
     * @var string
     */
    private $name;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }
}
