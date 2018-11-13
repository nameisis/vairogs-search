<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Bucketing;

use Vairogs\Utils\Search\Elastic\Aggregation\AbstractAggregation;
use Vairogs\Utils\Search\Elastic\Aggregation\Type\BucketingTrait;
use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use LogicException;

/**
 * @link https://goo.gl/DATnSs
 */
class FilterAggregation extends AbstractAggregation
{
    use BucketingTrait;

    /**
     * @var BuilderInterface
     */
    protected $filter;

    /**
     * @param string $name
     * @param BuilderInterface $filter
     */
    public function __construct($name, BuilderInterface $filter = null)
    {
        parent::__construct($name);
        if ($filter !== null) {
            $this->setFilter($filter);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        if (!$this->filter) {
            throw new LogicException("Filter aggregation `{$this->getName()}` has no filter added");
        }

        return $this->getFilter()->toArray();
    }

    /**
     * @return BuilderInterface
     */
    public function getFilter(): BuilderInterface
    {
        return $this->filter;
    }

    /**
     * @param BuilderInterface $filter
     */
    public function setFilter(BuilderInterface $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'filter';
    }

    /**
     * {@inheritdoc}
     */
    public function setField($field): void
    {
        throw new LogicException("Filter aggregation, doesn't support `field` parameter");
    }
}
