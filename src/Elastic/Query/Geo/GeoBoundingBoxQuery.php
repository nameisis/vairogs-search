<?php

namespace Vairogs\Utils\Search\Elastic\Query\Geo;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;
use LogicException;

/**
 * @link https://goo.gl/hptzB8
 */
class GeoBoundingBoxQuery implements BuilderInterface
{
    use ParametersTrait;

    /**
     * @var array
     */
    private $values;

    /**
     * @var string
     */
    private $field;

    /**
     * @param string $field
     * @param array $values
     * @param array $parameters
     */
    public function __construct($field, $values, array $parameters = [])
    {
        $this->field = $field;
        $this->values = $values;
        $this->setParameters($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'geo_bounding_box';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        if (\count($this->values) === 2) {
            $query = [
                $this->field => [
                    'top_left' => $this->values[0],
                    'bottom_right' => $this->values[1],
                ],
            ];
        } elseif (\count($this->values) === 4) {
            $query = [
                $this->field => [
                    'top' => $this->values[0],
                    'left' => $this->values[1],
                    'bottom' => $this->values[2],
                    'right' => $this->values[3],
                ],
            ];
        } else {
            throw new LogicException('Geo Bounding Box filter must have 2 or 4 geo points set.');
        }
        $output = $this->processArray($query);

        return [$this->getType() => $output];
    }
}
