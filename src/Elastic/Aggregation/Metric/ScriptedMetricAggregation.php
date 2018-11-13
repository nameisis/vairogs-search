<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Metric;

use Vairogs\Utils\Search\Elastic\Aggregation\AbstractAggregation;
use Vairogs\Utils\Search\Elastic\Aggregation\Type\MetricTrait;

/**
 * @link http://goo.gl/JbQsI3
 */
class ScriptedMetricAggregation extends AbstractAggregation
{
    use MetricTrait;

    /**
     * @var mixed
     */
    private $initScript;

    /**
     * @var mixed
     */
    private $mapScript;

    /**
     * @var mixed
     */
    private $combineScript;

    /**
     * @var mixed
     */
    private $reduceScript;

    /**
     * @param string $name
     * @param mixed $initScript
     * @param mixed $mapScript
     * @param mixed $combineScript
     * @param mixed $reduceScript
     */
    public function __construct($name, $initScript = null, $mapScript = null, $combineScript = null, $reduceScript = null)
    {

        parent::__construct($name);
        $this->setInitScript($initScript);
        $this->setMapScript($mapScript);
        $this->setCombineScript($combineScript);
        $this->setReduceScript($reduceScript);
    }

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        $out = \array_filter([
            'init_script' => $this->getInitScript(),
            'map_script' => $this->getMapScript(),
            'combine_script' => $this->getCombineScript(),
            'reduce_script' => $this->getReduceScript(),
        ]);

        return $out;
    }

    /**
     * @return mixed
     */
    public function getInitScript()
    {
        return $this->initScript;
    }

    /**
     * @param mixed $initScript
     */
    public function setInitScript($initScript): void
    {
        $this->initScript = $initScript;
    }

    /**
     * @return mixed
     */
    public function getMapScript()
    {
        return $this->mapScript;
    }

    /**
     * @param mixed $mapScript
     */
    public function setMapScript($mapScript): void
    {
        $this->mapScript = $mapScript;
    }

    /**
     * @return mixed
     */
    public function getCombineScript()
    {
        return $this->combineScript;
    }

    /**
     * @param mixed $combineScript
     */
    public function setCombineScript($combineScript): void
    {
        $this->combineScript = $combineScript;
    }

    /**
     * @return mixed
     */
    public function getReduceScript()
    {
        return $this->reduceScript;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'scripted_metric';
    }

    /**
     * @param mixed $reduceScript
     */
    public function setReduceScript($reduceScript): void
    {
        $this->reduceScript = $reduceScript;
    }
}
