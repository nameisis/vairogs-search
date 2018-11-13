<?php

namespace Vairogs\Utils\Search\Elastic\Query\Compound;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;

/**
 * @link https://goo.gl/1BLQkY
 */
class ConstantScoreQuery implements BuilderInterface
{
    use ParametersTrait;

    /**
     * @var BuilderInterface
     */
    private $query;

    /**
     * @param BuilderInterface $query
     * @param array $parameters
     */
    public function __construct(BuilderInterface $query, array $parameters = [])
    {
        $this->query = $query;
        $this->setParameters($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'constant_score';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $query = [
            'filter' => $this->query->toArray(),
        ];
        $output = $this->processArray($query);

        return [$this->getType() => $output];
    }
}
