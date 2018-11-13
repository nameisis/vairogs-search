<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Bucketing;

use Vairogs\Utils\Search\Elastic\Aggregation\AbstractAggregation;
use Vairogs\Utils\Search\Elastic\Aggregation\Type\BucketingTrait;
use LogicException;

/**
 * @link https://goo.gl/9gUVme
 */
class MissingAggregation extends AbstractAggregation
{
    use BucketingTrait;

    /**
     * @param string $name
     * @param string $field
     */
    public function __construct($name, $field = null)
    {
        parent::__construct($name);
        $this->setField($field);
    }

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        if ($this->getField()) {
            return ['field' => $this->getField()];
        }
        throw new LogicException('Missing aggregation must have a field set.');
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'missing';
    }
}
