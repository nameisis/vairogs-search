<?php

namespace Vairogs\Utils\Search\Elastic\Query\FullText;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;

/**
 * @link https://goo.gl/4pEmdr
 */
class MultiMatchQuery implements BuilderInterface
{
    use ParametersTrait;

    /**
     * @var array
     */
    private $fields;

    /**
     * @var string
     */
    private $query;

    /**
     * @param array $fields
     * @param string $query
     * @param array $parameters
     */
    public function __construct(array $fields, $query, array $parameters = [])
    {
        $this->fields = $fields;
        $this->query = $query;
        $this->setParameters($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'multi_match';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $query = [
            'fields' => $this->fields,
            'query' => $this->query,
        ];
        $output = $this->processArray($query);

        return [$this->getType() => $output];
    }
}
