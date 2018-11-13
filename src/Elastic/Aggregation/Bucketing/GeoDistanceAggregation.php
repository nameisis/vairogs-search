<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Bucketing;

use Vairogs\Utils\Search\Elastic\Aggregation\AbstractAggregation;
use Vairogs\Utils\Search\Elastic\Aggregation\Type\BucketingTrait;
use LogicException;

/**
 * @link https://goo.gl/RKiizf
 */
class GeoDistanceAggregation extends AbstractAggregation
{
    use BucketingTrait;

    /**
     * @var mixed
     */
    private $origin;

    /**
     * @var string
     */
    private $distanceType;

    /**
     * @var string
     */
    private $unit;

    /**
     * @var array
     */
    private $ranges = [];

    /**
     * @param string $name
     * @param string $field
     * @param mixed $origin
     * @param array $ranges
     * @param string $unit
     * @param string $distanceType
     */
    public function __construct($name, $field = null, $origin = null, $ranges = [], $unit = null, $distanceType = null)
    {
        parent::__construct($name);

        $this->setField($field);
        $this->setOrigin($origin);
        foreach ($ranges as $range) {
            $from = $range['from'] ?? null;
            $to = $range['to'] ?? null;
            $this->addRange($from, $to);
        }
        $this->setUnit($unit);
        $this->setDistanceType($distanceType);
    }

    /**
     * @param int|float|null $from
     * @param int|float|null $to
     *
     * @throws \LogicException
     *
     * @return GeoDistanceAggregation
     */
    public function addRange($from = null, $to = null): GeoDistanceAggregation
    {
        $range = \array_filter([
            'from' => $from,
            'to' => $to,
        ]);
        if (empty($range)) {
            throw new LogicException('Either from or to must be set. Both cannot be null.');
        }
        $this->ranges[] = $range;

        return $this;
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
            throw new LogicException('Geo distance aggregation must have a field set.');
        }
        if ($this->getOrigin()) {
            $data['origin'] = $this->getOrigin();
        } else {
            throw new LogicException('Geo distance aggregation must have an origin set.');
        }
        if ($this->getUnit()) {
            $data['unit'] = $this->getUnit();
        }
        if ($this->getDistanceType()) {
            $data['distance_type'] = $this->getDistanceType();
        }
        $data['ranges'] = $this->ranges;

        return $data;
    }

    /**
     * @return string
     */
    public function getOrigin(): string
    {
        return $this->origin;
    }

    /**
     * @param mixed $origin
     */
    public function setOrigin($origin): void
    {
        $this->origin = $origin;
    }

    /**
     * @return string
     */
    public function getUnit(): string
    {
        return $this->unit;
    }

    /**
     * @param string $unit
     */
    public function setUnit($unit): void
    {
        $this->unit = $unit;
    }

    /**
     * @return string
     */
    public function getDistanceType(): string
    {
        return $this->distanceType;
    }

    /**
     * @param string $distanceType
     */
    public function setDistanceType($distanceType): void
    {
        $this->distanceType = $distanceType;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'geo_distance';
    }
}
