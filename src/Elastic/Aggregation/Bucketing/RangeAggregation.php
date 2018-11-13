<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Bucketing;

use Vairogs\Utils\Search\Elastic\Aggregation\AbstractAggregation;
use Vairogs\Utils\Search\Elastic\Aggregation\Type\BucketingTrait;

/**
 * @link https://goo.gl/Ae4LAL
 */
class RangeAggregation extends AbstractAggregation
{
    use BucketingTrait;

    /**
     * @var array
     */
    private $ranges = [];

    /**
     * @var bool
     */
    private $keyed = false;

    /**
     * @param string $name
     * @param string $field
     * @param array $ranges
     * @param bool $keyed
     */
    public function __construct($name, $field = null, $ranges = [], $keyed = false)
    {
        parent::__construct($name);
        $this->setField($field);
        $this->setKeyed($keyed);
        foreach ($ranges as $range) {
            $this->addRange($range['from'] ?? null, $range['to'] ?? null, $range['key'] ?? '');
        }
    }

    /**
     * @param bool $keyed
     *
     * @return RangeAggregation
     */
    public function setKeyed($keyed): RangeAggregation
    {
        $this->keyed = $keyed;

        return $this;
    }

    /**
     * @param int|float|null $from
     * @param int|float|null $to
     * @param string $key
     *
     * @return RangeAggregation
     */
    public function addRange($from = null, $to = null, $key = ''): RangeAggregation
    {
        $range = \array_filter([
            'from' => $from,
            'to' => $to,
        ]);
        if ($this->keyed && !empty($key)) {
            $range['key'] = $key;
        }
        $this->ranges[] = $range;

        return $this;
    }

    /**
     * @param int|float|null $from
     * @param int|float|null $to
     *
     * @return bool
     */
    public function removeRange($from, $to): bool
    {
        foreach ($this->ranges as $key => $range) {
            if (\array_diff_assoc(\array_filter(['from' => $from, 'to' => $to]), $range) === []) {
                unset($this->ranges[$key]);

                return true;
            }
        }

        return false;
    }

    /**
     * @param string $key Range key.
     *
     * @return bool
     */
    public function removeRangeByKey($key): bool
    {
        if ($this->keyed) {
            foreach ($this->ranges as $rangeKey => $range) {
                if (\array_key_exists('key', $range) && $range['key'] === $key) {
                    unset($this->ranges[$rangeKey]);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        $data = [
            'keyed' => $this->keyed,
            'ranges' => \array_values($this->ranges),
        ];
        if ($this->getField()) {
            $data['field'] = $this->getField();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'range';
    }
}
