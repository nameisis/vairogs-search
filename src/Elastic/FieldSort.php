<?php

namespace Vairogs\Utils\Search\Elastic;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;
use stdClass;

class FieldSort implements BuilderInterface
{
    use ParametersTrait;

    public const ASC = 'asc';
    public const DESC = 'desc';

    /**
     * @var string
     */
    private $field;

    /**
     * @var string
     */
    private $order;

    /**
     * @var BuilderInterface
     */
    private $nestedFilter;

    /**
     * @param string $field
     * @param string $order
     * @param array $params
     */
    public function __construct($field, $order = null, array $params = [])
    {
        $this->field = $field;
        $this->order = $order;
        $this->setParameters($params);
    }

    /**
     * @return BuilderInterface
     */
    public function getNestedFilter(): BuilderInterface
    {
        return $this->nestedFilter;
    }

    /**
     * @param BuilderInterface $nestedFilter
     */
    public function setNestedFilter(BuilderInterface $nestedFilter): void
    {
        $this->nestedFilter = $nestedFilter;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return 'sort';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        if ($this->order) {
            $this->addParameter('order', $this->order);
        }
        if ($this->nestedFilter) {
            $this->addParameter('nested_filter', $this->nestedFilter->toArray());
        }
        $output = [
            $this->field => $this->getParameters() ?? new stdClass(),
        ];

        return $output;
    }
}
