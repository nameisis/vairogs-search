<?php

namespace Vairogs\Utils\Search\Elastic\Query\Joining;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;

/**
 * @link https://goo.gl/vv3CZG
 */
class HasParentQuery implements BuilderInterface
{
    use ParametersTrait;

    /**
     * @var string
     */
    private $parentType;

    /**
     * @var BuilderInterface
     */
    private $query;

    /**
     * @param string $parentType
     * @param BuilderInterface $query
     * @param array $parameters
     */
    public function __construct($parentType, BuilderInterface $query, array $parameters = [])
    {
        $this->parentType = $parentType;
        $this->query = $query;
        $this->setParameters($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'has_parent';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $query = [
            'parent_type' => $this->parentType,
            'query' => $this->query->toArray(),
        ];
        $output = $this->processArray($query);

        return [$this->getType() => $output];
    }
}
