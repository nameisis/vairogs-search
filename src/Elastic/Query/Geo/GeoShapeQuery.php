<?php

namespace Vairogs\Utils\Search\Elastic\Query\Geo;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;

/**
 * @link https://goo.gl/hJWnPG
 */
class GeoShapeQuery implements BuilderInterface
{
    use ParametersTrait;

    public const INTERSECTS = 'intersects';
    public const DISJOINT = 'disjoint';
    public const WITHIN = 'within';
    public const CONTAINS = 'contains';

    /**
     * @var array
     */
    private $fields = [];

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->setParameters($parameters);
    }

    /**
     * @param string $field
     * @param string $type
     * @param array $coordinates
     * @param string $relation
     * @param array $parameters
     */
    public function addShape($field, $type, array $coordinates, $relation = self::INTERSECTS, array $parameters = []): void
    {
        $filter = \array_merge($parameters, [
            'type' => $type,
            'coordinates' => $coordinates,
        ]);
        $this->fields[$field] = [
            'shape' => $filter,
            'relation' => $relation,
        ];
    }

    /**
     * @param string $field
     * @param string $id
     * @param string $type
     * @param string $index
     * @param string $path
     * @param array $parameters
     */
    public function addPreIndexedShape($field, $id, $type, $index, $path, array $parameters = []): void
    {
        $filter = \array_merge($parameters, [
            'id' => $id,
            'type' => $type,
            'index' => $index,
            'path' => $path,
        ]);
        $this->fields[$field]['indexed_shape'] = $filter;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'geo_shape';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $output = $this->processArray($this->fields);

        return [$this->getType() => $output];
    }
}
