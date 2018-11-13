<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Bucketing;

use Vairogs\Utils\Search\Elastic\Aggregation\AbstractAggregation;
use Vairogs\Utils\Search\Elastic\Aggregation\Type\BucketingTrait;
use LogicException;

/**
 * @link https://goo.gl/i5VXf9
 */
class Ipv4RangeAggregation extends AbstractAggregation
{
    use BucketingTrait;

    /**
     * @var array
     */
    private $ranges = [];

    /**
     * @param string $name
     * @param string $field
     * @param array $ranges
     */
    public function __construct($name, $field = null, $ranges = [])
    {
        parent::__construct($name);
        $this->setField($field);
        foreach ($ranges as $range) {
            if (\is_array($range)) {
                $from = $range['from'] ?? null;
                $to = $range['to'] ?? null;
                $this->addRange($from, $to);
            } else {
                $this->addMask($range);
            }
        }
    }

    /**
     * @param string|null $from
     * @param string|null $to
     *
     * @return Ipv4RangeAggregation
     */
    public function addRange($from = null, $to = null): Ipv4RangeAggregation
    {
        $range = \array_filter([
            'from' => $from,
            'to' => $to,
        ]);
        $this->ranges[] = $range;

        return $this;
    }

    /**
     * @param string $mask
     *
     * @return Ipv4RangeAggregation
     */
    public function addMask($mask): Ipv4RangeAggregation
    {
        $this->ranges[] = ['mask' => $mask];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        if (!empty($this->ranges) && $this->getField()) {
            return [
                'field' => $this->getField(),
                'ranges' => \array_values($this->ranges),
            ];
        }
        throw new LogicException('Ip range aggregation must have field set and range added.');
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'ip_range';
    }
}
