<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Bucketing;

use Vairogs\Utils\Search\Elastic\Aggregation\AbstractAggregation;
use Vairogs\Utils\Search\Elastic\Aggregation\Type\BucketingTrait;
use LogicException;

/**
 * @link https://goo.gl/Uuq6CL
 */
class DateRangeAggregation extends AbstractAggregation
{
    use BucketingTrait;

    /**
     * @var string
     */
    private $format;
    /**
     * @var array
     */
    private $ranges = [];

    /**
     * @param string $name
     * @param string $field
     * @param string $format
     * @param array $ranges
     */
    public function __construct($name, $field = null, $format = null, array $ranges = [])
    {
        parent::__construct($name);
        $this->setField($field);
        $this->setFormat($format);
        foreach ($ranges as $range) {
            $from = $range['from'] ?? null;
            $to = $range['to'] ?? null;
            $key = $range['key'] ?? null;
            $this->addRange($from, $to, $key);
        }
    }

    /**
     * @param string|null $from
     * @param string|null $to
     *
     * @param string|null $key
     *
     * @return $this
     *
     */
    public function addRange($from = null, $to = null, $key = null): self
    {
        $range = \array_filter([
            'from' => $from,
            'to' => $to,
            'key' => $key,
        ]);
        if (empty($range)) {
            throw new LogicException('Either from or to must be set. Both cannot be null.');
        }
        $this->ranges[] = $range;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        if (!empty($this->ranges) && $this->getField() && $this->getFormat()) {
            $data = [
                'format' => $this->getFormat(),
                'field' => $this->getField(),
                'ranges' => $this->ranges,
            ];

            return $data;
        }
        throw new LogicException('Date range aggregation must have field, format set and range added.');
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @param string $format
     */
    public function setFormat($format): void
    {
        $this->format = $format;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'date_range';
    }
}
