<?php

namespace Vairogs\Utils\Search\Elastic\Query\Span;

use InvalidArgumentException;
use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;

/**
 * @link https://goo.gl/zHzar2
 */
class SpanMultiTermQuery implements SpanQueryInterface
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
        return 'span_multi';
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function toArray(): array
    {
        $query = [
            'match' => $this->query->toArray(),
        ];
        $output = $this->processArray($query);

        return [$this->getType() => $output];
    }
}
