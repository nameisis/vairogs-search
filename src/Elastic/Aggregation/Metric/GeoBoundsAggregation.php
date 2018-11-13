<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Metric;

use Vairogs\Utils\Search\Elastic\Aggregation\AbstractAggregation;
use Vairogs\Utils\Search\Elastic\Aggregation\Type\MetricTrait;
use LogicException;

/**
 * @link http://goo.gl/aGqw7Y
 */
class GeoBoundsAggregation extends AbstractAggregation
{
    use MetricTrait;

    /**
     * @var bool
     */
    private $wrapLongitude = true;

    /**
     * @param string $name
     * @param string $field
     * @param bool $wrapLongitude
     */
    public function __construct($name, $field = null, $wrapLongitude = true)
    {
        parent::__construct($name);
        $this->setField($field);
        $this->setWrapLongitude($wrapLongitude);
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
            throw new LogicException('Geo bounds aggregation must have a field set.');
        }
        $data['wrap_longitude'] = $this->isWrapLongitude();

        return $data;
    }

    /**
     * @return bool
     */
    public function isWrapLongitude(): bool
    {
        return $this->wrapLongitude;
    }

    /**
     * @param bool $wrapLongitude
     */
    public function setWrapLongitude($wrapLongitude): void
    {
        $this->wrapLongitude = $wrapLongitude;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'geo_bounds';
    }
}
