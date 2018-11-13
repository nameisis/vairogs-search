<?php

namespace Vairogs\Utils\Search\Elastic\Query\FullText;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;

/**
 * @link https://goo.gl/7RPeVz
 */
class QueryStringQuery implements BuilderInterface
{
    use ParametersTrait;

    /**
     * @var string
     */
    private $query;

    /**
     * @param string $query
     * @param array $parameters
     */
    public function __construct($query, array $parameters = [])
    {
        $this->query = $query;
        $this->setParameters($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'query_string';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $query = [
            'query' => $this->query,
        ];
        $output = $this->processArray($query);

        return [$this->getType() => $output];
    }
}
