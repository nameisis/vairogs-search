<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Bucketing;

use Vairogs\Utils\Search\Elastic\Aggregation\AbstractAggregation;
use Vairogs\Utils\Search\Elastic\Aggregation\Type\BucketingTrait;
use LogicException;
use stdClass;

/**
 * @link https://goo.gl/hzPwPU
 */
class GlobalAggregation extends AbstractAggregation
{
    use BucketingTrait;

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        return new stdClass();
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'global';
    }

    /**
     * {@inheritdoc}
     */
    public function setField($field): void
    {
        throw new LogicException("Global aggregation, doesn't support `field` parameter");
    }
}
