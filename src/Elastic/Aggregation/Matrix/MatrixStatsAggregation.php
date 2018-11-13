<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Matrix;

use Vairogs\Utils\Search\Elastic\Aggregation\AbstractAggregation;
use Vairogs\Utils\Search\Elastic\Aggregation\Type\MetricTrait;

/**
 * @link https://goo.gl/Cem86S
 */
class MatrixStatsAggregation extends AbstractAggregation
{
    use MetricTrait;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var array
     */
    private $missing;

    /**
     * @param string $name
     * @param string $field
     * @param array $missing
     * @param string $mode
     */
    public function __construct($name, $field, $missing = null, $mode = null)
    {
        parent::__construct($name);
        $this->setField($field);
        $this->setMode($mode);
        $this->missing = $missing;
    }

    protected function getArray()
    {
        $out = [];
        if ($this->getField()) {
            $out['fields'] = $this->getField();
        }
        if ($this->getMode()) {
            $out['mode'] = $this->getMode();
        }
        if ($this->getMissing()) {
            $out['missing'] = $this->getMissing();
        }

        return $out;
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     */
    public function setMode($mode): void
    {
        $this->mode = $mode;
    }

    /**
     * @return array
     */
    public function getMissing(): array
    {
        return $this->missing;
    }

    /**
     * @param array $missing
     */
    public function setMissing($missing): void
    {
        $this->missing = $missing;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'matrix_stats';
    }
}
