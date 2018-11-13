<?php

namespace Vairogs\Utils\Search\Elastic\Aggregation\Pipeline;

use LogicException;

/**
 * @link https://goo.gl/miVxcx
 */
class BucketScriptAggregation extends AbstractPipelineAggregation
{
    /**
     * @var string
     */
    private $script;

    /**
     * @param string $name
     * @param array $bucketsPath
     * @param string $script
     */
    public function __construct($name, $bucketsPath, $script = null)
    {
        parent::__construct($name, $bucketsPath);
        $this->setScript($script);
    }

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        if (!$this->getScript()) {
            throw new LogicException(\sprintf('`%s` aggregation must have script set.', $this->getName()));
        }
        $out = [
            'buckets_path' => $this->getBucketsPath(),
            'script' => $this->getScript(),
        ];

        return $out;
    }

    /**
     * @return string
     */
    public function getScript(): string
    {
        return $this->script;
    }

    /**
     * @param string $script
     */
    public function setScript($script): void
    {
        $this->script = $script;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'bucket_script';
    }
}
