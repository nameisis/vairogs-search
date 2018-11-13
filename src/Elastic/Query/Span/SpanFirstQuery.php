<?php

namespace Vairogs\Utils\Search\Elastic\Query\Span;

use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;
use LogicException;

/**
 * @link https://goo.gl/7Qp9hs
 */
class SpanFirstQuery implements SpanQueryInterface
{
    use ParametersTrait;

    /**
     * @var SpanQueryInterface
     */
    private $query;

    /**
     * @var int
     */
    private $end;

    /**
     * @param SpanQueryInterface $query
     * @param int $end
     * @param array $parameters
     *
     * @throws LogicException
     */
    public function __construct(SpanQueryInterface $query, $end, array $parameters = [])
    {
        $this->query = $query;
        $this->end = $end;
        $this->setParameters($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'span_first';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $query = [
            'match' => $this->query->toArray(),
            'end' => $this->end,
        ];
        $output = $this->processArray($query);

        return [$this->getType() => $output];
    }
}
