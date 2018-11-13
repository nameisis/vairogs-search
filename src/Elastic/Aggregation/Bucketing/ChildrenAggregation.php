<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Bucketing;

use Vairogs\Utils\Search\Elastic\Aggregation\AbstractAggregation;
use Vairogs\Utils\Search\Elastic\Aggregation\Type\BucketingTrait;
use LogicException;

/**
 * @link https://goo.gl/Ryd528
 */
class ChildrenAggregation extends AbstractAggregation
{
    use BucketingTrait;

    /**
     * @var string
     */
    private $children;

    /**
     * @param string $name
     * @param string $children
     */
    public function __construct($name, $children = null)
    {
        parent::__construct($name);
        $this->setChildren($children);
    }

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        if (\count($this->getAggregations()) === 0) {
            throw new LogicException("Children aggregation `{$this->getName()}` has no aggregations added");
        }

        return ['type' => $this->getChildren()];
    }

    /**
     * @return string
     */
    public function getChildren(): string
    {
        return $this->children;
    }

    /**
     * @param string $children
     */
    public function setChildren($children): void
    {
        $this->children = $children;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'children';
    }
}
