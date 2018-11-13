<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Bucketing;

use Vairogs\Utils\Search\Elastic\Aggregation\AbstractAggregation;
use Vairogs\Utils\Search\Elastic\Aggregation\Type\BucketingTrait;

/**
 * @link https://goo.gl/ARgr5n
 */
class NestedAggregation extends AbstractAggregation
{
    use BucketingTrait;

    /**
     * @var string
     */
    private $path;

    /**
     * @param string $name
     * @param string $path
     */
    public function __construct($name, $path = null)
    {
        parent::__construct($name);
        $this->setPath($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        return ['path' => $this->getPath()];
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path): void
    {
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'nested';
    }
}
