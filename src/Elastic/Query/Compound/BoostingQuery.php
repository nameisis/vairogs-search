<?php

namespace Vairogs\Utils\Search\Elastic\Query\Compound;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;

/**
 * @link https://goo.gl/qGor1C
 */
class BoostingQuery implements BuilderInterface
{
    /**
     * @var BuilderInterface
     */
    private $positive;

    /**
     * @var BuilderInterface
     */
    private $negative;

    /**
     * @var int|float
     */
    private $negativeBoost;

    /**
     * @param BuilderInterface $positive
     * @param BuilderInterface $negative
     * @param int|float $negativeBoost
     */
    public function __construct(BuilderInterface $positive, BuilderInterface $negative, $negativeBoost)
    {
        $this->positive = $positive;
        $this->negative = $negative;
        $this->negativeBoost = $negativeBoost;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'boosting';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $query = [
            'positive' => $this->positive->toArray(),
            'negative' => $this->negative->toArray(),
            'negative_boost' => $this->negativeBoost,
        ];

        return [$this->getType() => $query];
    }
}
