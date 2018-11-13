<?php

namespace Vairogs\Utils\Search\Elastic\Query\Specialized;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;

/**
 * @link https://goo.gl/Xob3pF
 */
class ScriptQuery implements BuilderInterface
{
    use ParametersTrait;

    /**
     * @var string
     */
    private $script;

    /**
     * @param string $script Script
     * @param array $parameters
     */
    public function __construct($script, array $parameters = [])
    {
        $this->script = $script;
        $this->setParameters($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'script';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $query = ['inline' => $this->script];
        $output = $this->processArray($query);

        return [$this->getType() => ['script' => $output]];
    }
}
