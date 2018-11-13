<?php

namespace Vairogs\Utils\Search\Elastic\Query\Joining;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;

/**
 * @link https://goo.gl/2LdGCa
 */
class HasChildQuery implements BuilderInterface
{
    use ParametersTrait;

    /**
     * @var string
     */
    private $type;

    /**
     * @var BuilderInterface
     */
    private $query;

    /**
     * @param string $type
     * @param BuilderInterface $query
     * @param array $parameters
     */
    public function __construct($type, BuilderInterface $query, array $parameters = [])
    {
        $this->type = $type;
        $this->query = $query;
        $this->setParameters($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'has_child';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $query = [
            'type' => $this->type,
            'query' => $this->query->toArray(),
        ];
        $output = $this->processArray($query);

        return [$this->getType() => $output];
    }
}
