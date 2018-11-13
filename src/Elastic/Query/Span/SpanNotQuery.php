<?php

namespace Vairogs\Utils\Search\Elastic\Query\Span;

use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;

/**
 * @link https://goo.gl/M8kRc8
 */
class SpanNotQuery implements SpanQueryInterface
{
    use ParametersTrait;

    /**
     * @var SpanQueryInterface
     */
    private $include;

    /**
     * @var SpanQueryInterface
     */
    private $exclude;

    /**
     * @param SpanQueryInterface $include
     * @param SpanQueryInterface $exclude
     * @param array $parameters
     */
    public function __construct(SpanQueryInterface $include, SpanQueryInterface $exclude, array $parameters = [])
    {
        $this->include = $include;
        $this->exclude = $exclude;
        $this->setParameters($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'span_not';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $query = [
            'include' => $this->include->toArray(),
            'exclude' => $this->exclude->toArray(),
        ];

        return [$this->getType() => $this->processArray($query)];
    }
}
