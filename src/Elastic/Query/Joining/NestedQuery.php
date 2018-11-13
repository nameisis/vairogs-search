<?php

namespace Vairogs\Utils\Search\Elastic\Query\Joining;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;

/**
 * @link https://goo.gl/PxKMi1
 */
class NestedQuery implements BuilderInterface
{
    use ParametersTrait;

    /**
     * @var string
     */
    private $path;

    /**
     * @var BuilderInterface
     */
    private $query;

    /**
     * @param string $path
     * @param BuilderInterface $query
     * @param array $parameters
     */
    public function __construct($path, BuilderInterface $query, array $parameters = [])
    {
        $this->path = $path;
        $this->query = $query;
        $this->parameters = $parameters;
    }

    /**
     * @return BuilderInterface
     */
    public function getQuery(): BuilderInterface
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'nested';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            $this->getType() => $this->processArray([
                'path' => $this->path,
                'query' => $this->query->toArray(),
            ]),
        ];
    }

}
