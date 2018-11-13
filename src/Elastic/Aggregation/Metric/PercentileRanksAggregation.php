<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Metric;

use Vairogs\Utils\Search\Elastic\Aggregation\AbstractAggregation;
use Vairogs\Utils\Search\Elastic\Aggregation\Type\MetricTrait;
use Vairogs\Utils\Search\Elastic\Component\ScriptAwareTrait;
use LogicException;

/**
 * @link https://goo.gl/zhNkHS
 */
class PercentileRanksAggregation extends AbstractAggregation
{
    use MetricTrait;
    use ScriptAwareTrait;

    /**
     * @var array
     */
    private $values;

    /**
     * @var int
     */
    private $compression;

    /**
     * @param string $name
     * @param string $field
     * @param array $values
     * @param string $script
     * @param int $compression
     */
    public function __construct($name, $field = null, $values = null, $script = null, $compression = null)
    {
        parent::__construct($name);
        $this->setField($field);
        $this->setValues($values);
        $this->setScript($script);
        $this->setCompression($compression);
    }

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        $out = \array_filter([
            'field' => $this->getField(),
            'script' => $this->getScript(),
            'values' => $this->getValues(),
            'compression' => $this->getCompression(),
        ], function($val) {
            return ($val || \is_numeric($val));
        });
        $this->isRequiredParametersSet($out);

        return $out;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @param array $values
     */
    public function setValues($values): void
    {
        $this->values = $values;
    }

    /**
     * @return int
     */
    public function getCompression(): int
    {
        return $this->compression;
    }

    /**
     * @param int $compression
     */
    public function setCompression($compression): void
    {
        $this->compression = $compression;
    }

    /**
     * @param array $a
     *
     * @return bool
     * @throws \LogicException
     */
    private function isRequiredParametersSet($a): bool
    {
        if (\array_key_exists('values', $a)) {
            if (\array_key_exists('field', $a) || \array_key_exists('script', $a)) {
                return true;
            }
        }

        throw new LogicException('Percentile ranks aggregation must have field and values or script and values set.');
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'percentile_ranks';
    }
}
