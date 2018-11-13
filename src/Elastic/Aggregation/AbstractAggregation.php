<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation;

use Vairogs\Utils\Search\Elastic\BuilderPool;
use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Component\NameAwareTrait;
use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;
use stdClass;

abstract class AbstractAggregation implements BuilderInterface
{
    use ParametersTrait;
    use NameAwareTrait;

    /**
     * @var string
     */
    private $field;

    /**
     * @var BuilderPool
     */
    private $aggregations;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->setName($name);
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @param string $field
     */
    public function setField($field): void
    {
        $this->field = $field;
    }

    /**
     * @param AbstractAggregation $abstractAggregation
     *
     * @return $this
     */
    public function addAggregation(AbstractAggregation $abstractAggregation): self
    {
        if (!$this->aggregations) {
            $this->aggregations = $this->createBuilderBag();
        }
        $this->aggregations->add($abstractAggregation);

        return $this;
    }

    /**
     * @return BuilderPool
     */
    private function createBuilderBag(): BuilderPool
    {
        return new BuilderPool();
    }

    /**
     * @param string $name
     *
     * @return BuilderInterface|null
     */
    public function getAggregation($name): ?BuilderInterface
    {
        if ($this->aggregations && $this->aggregations->has($name)) {
            return $this->aggregations->get($name);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $array = $this->getArray();
        $result = [
            $this->getType() => \is_array($array) ? $this->processArray($array) : $array,
        ];
        if ($this->supportsNesting()) {
            $nestedResult = $this->collectNestedAggregations();
            if (!empty($nestedResult)) {
                $result['aggregations'] = $nestedResult;
            }
        }

        return $result;
    }

    /**
     * @return array|stdClass
     */
    abstract protected function getArray();

    /**
     * @return bool
     */
    abstract protected function supportsNesting(): bool;

    /**
     * @return array
     */
    protected function collectNestedAggregations(): array
    {
        $result = [];
        foreach ($this->getAggregations() as $aggregation) {
            /** @var AbstractAggregation $aggregation */
            $result[$aggregation->getName()] = $aggregation->toArray();
        }

        return $result;
    }

    /**
     * @return BuilderPool[]
     */
    public function getAggregations(): array
    {
        if ($this->aggregations) {
            return $this->aggregations->all();
        }

        return [];
    }
}
