<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Bucketing;

use Vairogs\Utils\Search\Elastic\Aggregation\AbstractAggregation;
use Vairogs\Utils\Search\Elastic\Aggregation\Type\BucketingTrait;
use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use LogicException;

/**
 * @link https://goo.gl/YnK27R
 */
class FiltersAggregation extends AbstractAggregation
{
    use BucketingTrait;

    /**
     * @var BuilderInterface[]
     */
    private $filters = [];

    /**
     * @var bool
     */
    private $anonymous = false;

    /**
     * @param string $name
     * @param BuilderInterface[] $filters
     * @param bool $anonymous
     */
    public function __construct($name, $filters = [], $anonymous = false)
    {
        parent::__construct($name);

        $this->setAnonymous($anonymous);
        foreach ($filters as $key => $filter) {
            if ($anonymous) {
                $this->addFilter($filter);
            } else {
                $this->addFilter($filter, $key);
            }
        }
    }

    /**
     * @param bool $anonymous
     *
     * @return FiltersAggregation
     */
    public function setAnonymous($anonymous): FiltersAggregation
    {
        $this->anonymous = $anonymous;

        return $this;
    }

    /**
     * @param BuilderInterface $filter
     * @param string $name
     *
     * @throws LogicException
     *
     * @return FiltersAggregation
     */
    public function addFilter(BuilderInterface $filter, $name = ''): FiltersAggregation
    {
        if ($this->anonymous === false && empty($name)) {
            throw new LogicException('In not anonymous filters filter name must be set.');
        }

        if ($this->anonymous === false && !empty($name)) {
            $this->filters['filters'][$name] = $filter->toArray();
        } else {
            $this->filters['filters'][] = $filter->toArray();
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        return $this->filters;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'filters';
    }
}
