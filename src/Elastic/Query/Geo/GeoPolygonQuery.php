<?php

namespace Vairogs\Utils\Search\Elastic\Query\Geo;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;

/**
 * @link https://goo.gl/Pgksf8
 */
class GeoPolygonQuery implements BuilderInterface
{
    use ParametersTrait;

    /**
     * @var string
     */
    private $field;

    /**
     * @var array
     */
    private $points;

    /**
     * @param string $field
     * @param array $points
     * @param array $parameters
     */
    public function __construct($field, array $points = [], array $parameters = [])
    {
        $this->field = $field;
        $this->points = $points;
        $this->setParameters($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'geo_polygon';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $query = [$this->field => ['points' => $this->points]];
        $output = $this->processArray($query);

        return [$this->getType() => $output];
    }
}
