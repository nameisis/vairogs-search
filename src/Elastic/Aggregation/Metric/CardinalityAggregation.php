<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Metric;

use Vairogs\Utils\Search\Elastic\Aggregation\AbstractAggregation;
use Vairogs\Utils\Search\Elastic\Aggregation\Type\MetricTrait;
use Vairogs\Utils\Search\Elastic\Component\ScriptAwareTrait;
use LogicException;

/**
 * @link http://goo.gl/tG7ciG
 */
class CardinalityAggregation extends AbstractAggregation
{
    use MetricTrait;
    use ScriptAwareTrait;

    /**
     * @var int
     */
    private $precisionThreshold;

    /**
     * @var bool
     */
    private $rehash;

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        $out = \array_filter([
            'field' => $this->getField(),
            'script' => $this->getScript(),
            'precision_threshold' => $this->getPrecisionThreshold(),
            'rehash' => $this->isRehash(),
        ], function($val) {
            return ($val || \is_bool($val));
        });
        $this->checkRequiredFields($out);

        return $out;
    }

    /**
     * @return int
     */
    public function getPrecisionThreshold(): int
    {
        return $this->precisionThreshold;
    }

    /**
     * @param int $precision
     */
    public function setPrecisionThreshold($precision): void
    {
        $this->precisionThreshold = $precision;
    }

    /**
     * @return bool
     */
    public function isRehash(): bool
    {
        return $this->rehash;
    }

    /**
     * @param bool $rehash
     */
    public function setRehash($rehash): void
    {
        $this->rehash = $rehash;
    }

    /**
     * @param array $fields
     *
     * @throws LogicException
     */
    private function checkRequiredFields($fields): void
    {
        if (!\array_key_exists('field', $fields) && !\array_key_exists('script', $fields)) {
            throw new LogicException('Cardinality aggregation must have field or script set.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'cardinality';
    }
}
