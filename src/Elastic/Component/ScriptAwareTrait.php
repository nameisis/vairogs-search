<?php

namespace Vairogs\Utils\Search\Elastic\Component;

trait ScriptAwareTrait
{
    /**
     * @var string
     */
    private $script;

    /**
     * @return string
     */
    public function getScript(): string
    {
        return $this->script;
    }

    /**
     * @param string $script
     */
    public function setScript($script): void
    {
        $this->script = $script;
    }
}
