<?php

namespace Vairogs\Utils\Search\Elastic\Query\Span;

use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;

/**
 * @link https://goo.gl/x8y5z3
 */
class SpanContainingQuery implements SpanQueryInterface
{
    use ParametersTrait;

    /**
     * @param SpanQueryInterface
     */
    private $little;

    /**
     * @param SpanQueryInterface
     */
    private $big;

    /**
     * @param SpanQueryInterface $little
     * @param SpanQueryInterface $big
     */
    public function __construct(SpanQueryInterface $little, SpanQueryInterface $big)
    {
        $this->setLittle($little);
        $this->setBig($big);
    }

    /**
     * @return SpanQueryInterface
     */
    public function getLittle(): SpanQueryInterface
    {
        return $this->little;
    }

    /**
     * @param SpanQueryInterface $little
     */
    public function setLittle(SpanQueryInterface $little): void
    {
        $this->little = $little;
    }

    /**
     * @return SpanQueryInterface
     */
    public function getBig(): SpanQueryInterface
    {
        return $this->big;
    }

    /**
     * @param SpanQueryInterface $big
     */
    public function setBig(SpanQueryInterface $big): void
    {
        $this->big = $big;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'span_containing';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $output = [
            'little' => $this->getLittle()->toArray(),
            'big' => $this->getBig()->toArray(),
        ];
        $output = $this->processArray($output);

        return [$this->getType() => $output];
    }
}
