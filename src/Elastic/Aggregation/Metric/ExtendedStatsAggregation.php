<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Metric;

use Vairogs\Utils\Search\Elastic\Aggregation\AbstractAggregation;
use Vairogs\Utils\Search\Elastic\Aggregation\Type\MetricTrait;
use Vairogs\Utils\Search\Elastic\Component\ScriptAwareTrait;

/**
 * @link http://goo.gl/E0PpDv
 */
class ExtendedStatsAggregation extends AbstractAggregation
{
    use MetricTrait;
    use ScriptAwareTrait;

    /**
     * @var int
     */
    private $sigma;

    /**
     * @param string $name
     * @param string $field
     * @param int $sigma
     * @param string $script
     */
    public function __construct($name, $field = null, $sigma = null, $script = null)
    {
        parent::__construct($name);
        $this->setField($field);
        $this->setSigma($sigma);
        $this->setScript($script);
    }

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        $out = \array_filter([
            'field' => $this->getField(),
            'script' => $this->getScript(),
            'sigma' => $this->getSigma(),
        ], function($val) {
            return ($val || \is_numeric($val));
        });

        return $out;
    }

    /**
     * @return int
     */
    public function getSigma(): int
    {
        return $this->sigma;
    }

    /**
     * @param int $sigma
     */
    public function setSigma($sigma): void
    {
        $this->sigma = $sigma;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'extended_stats';
    }
}
