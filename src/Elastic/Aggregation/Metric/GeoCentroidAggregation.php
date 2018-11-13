<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Metric;

use Vairogs\Utils\Search\Elastic\Aggregation\AbstractAggregation;
use Vairogs\Utils\Search\Elastic\Aggregation\Type\MetricTrait;
use LogicException;

/**
 * @link https://goo.gl/GAmkYJ
 */
class GeoCentroidAggregation extends AbstractAggregation
{
    use MetricTrait;

    /**
     * @param string $name
     * @param string $field
     */
    public function __construct($name, $field = null)
    {
        parent::__construct($name);
        $this->setField($field);
    }

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        $data = [];
        if ($this->getField()) {
            $data['field'] = $this->getField();
        } else {
            throw new LogicException('Geo centroid aggregation must have a field set.');
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'geo_centroid';
    }
}
