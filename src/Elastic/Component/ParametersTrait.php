<?php

namespace Vairogs\Utils\Search\Elastic\Component;

use stdClass;

trait ParametersTrait
{
    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @param string $name
     */
    public function removeParameter($name): void
    {
        if ($this->hasParameter($name)) {
            unset($this->parameters[$name]);
        }
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasParameter($name): bool
    {
        return isset($this->parameters[$name]);
    }

    /**
     * @param string $name
     *
     * @return array|false
     */
    public function getParameter($name)
    {
        return $this->parameters[$name];
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * @param string $name
     * @param array|string|stdClass $value
     */
    public function addParameter($name, $value): void
    {
        $this->parameters[$name] = $value;
    }

    /**
     * @param array $array
     *
     * @return array
     */
    protected function processArray(array $array = []): array
    {
        return \array_merge($array, $this->parameters);
    }
}
