<?php

namespace Vairogs\Utils\Search\Elastic\Query\Span;

/**
 * @link https://goo.gl/WqVLwR
 */
class SpanNearQuery extends SpanOrQuery
{
    /**
     * @var int
     */
    private $slop;

    /**
     * @return int
     */
    public function getSlop(): int
    {
        return $this->slop;
    }

    /**
     * @param int $slop
     */
    public function setSlop($slop): void
    {
        $this->slop = $slop;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'span_near';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $query = [];
        foreach ($this->getQueries() as $type) {
            $query['clauses'][] = $type->toArray();
        }
        $query['slop'] = $this->getSlop();
        $output = $this->processArray($query);

        return [$this->getType() => $output];
    }
}
