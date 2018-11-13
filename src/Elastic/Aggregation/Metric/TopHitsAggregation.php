<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Metric;

use Vairogs\Utils\Search\Elastic\Aggregation\AbstractAggregation;
use Vairogs\Utils\Search\Elastic\Aggregation\Type\MetricTrait;
use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use stdClass;

/**
 * @link https://goo.gl/J7gZYm
 */
class TopHitsAggregation extends AbstractAggregation
{
    use MetricTrait;

    /**
     * @var int
     */
    private $size;

    /**
     * @var int
     */
    private $from;

    /**
     * @var BuilderInterface[]
     */
    private $sorts = [];

    /**
     * @param string $name
     * @param null|int $size
     * @param null|int $from
     * @param null|BuilderInterface $sort
     */
    public function __construct($name, $size = null, $from = null, $sort = null)
    {
        parent::__construct($name);
        $this->setFrom($from);
        $this->setSize($size);
        $this->addSort($sort);
    }

    /**
     * @param BuilderInterface $sort
     */
    public function addSort($sort): void
    {
        $this->sorts[] = $sort;
    }

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        $addedSorts = \array_filter($this->getSorts()) ?? [];
        $sortsOutput = null;
        foreach ($addedSorts as $sort) {
            /** @var BuilderInterface $sort */
            $sortsOutput[] = $sort->toArray();
        }
        $output = \array_filter([
            'sort' => $sortsOutput,
            'size' => $this->getSize(),
            'from' => $this->getFrom(),
        ], function($val) {
            return ($val || \is_array($val) || ($val || \is_numeric($val)));
        });

        return empty($output) ? new stdClass() : $output;
    }

    /**
     * @return BuilderInterface[]
     */
    public function getSorts(): array
    {
        return $this->sorts;
    }

    /**
     * @param BuilderInterface[] $sorts
     */
    public function setSorts(array $sorts): void
    {
        $this->sorts = $sorts;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function setSize($size): void
    {
        $this->size = $size;
    }

    /**
     * @return int
     */
    public function getFrom(): int
    {
        return $this->from;
    }

    /**
     * @param int $from
     */
    public function setFrom($from): void
    {
        $this->from = $from;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'top_hits';
    }
}
