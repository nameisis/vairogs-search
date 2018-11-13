<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Pipeline;

/**
 * @link https://goo.gl/bqi7m5
 */
class PercentilesBucketAggregation extends AbstractPipelineAggregation
{
    /**
     * @var array
     */
    private $percents;

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        $data = ['buckets_path' => $this->getBucketsPath()];
        if ($this->getPercents()) {
            $data['percents'] = $this->getPercents();
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getPercents(): array
    {
        return $this->percents;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'percentiles_bucket';
    }

    /**
     * @param array $percents
     */
    public function setPercents(array $percents): void
    {
        $this->percents = $percents;
    }
}
