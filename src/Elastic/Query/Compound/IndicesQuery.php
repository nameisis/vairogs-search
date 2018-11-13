<?php

namespace Vairogs\Utils\Search\Elastic\Query\Compound;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;

/**
 * @link https://goo.gl/a3FNzL
 */
class IndicesQuery implements BuilderInterface
{
    /**
     * @var string[]
     */
    private $indices;

    /**
     * @var BuilderInterface
     */
    private $query;

    /**
     * @var string|BuilderInterface
     */
    private $noMatchQuery;

    /**
     * @param string[] $indices
     * @param BuilderInterface $query
     * @param BuilderInterface $noMatchQuery
     */
    public function __construct($indices, $query, $noMatchQuery = null)
    {
        $this->indices = $indices;
        $this->query = $query;
        $this->noMatchQuery = $noMatchQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'indices';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        if (\count($this->indices) > 1) {
            $output = ['indices' => $this->indices];
        } else {
            $output = ['index' => $this->indices[0]];
        }
        $output['query'] = $this->query->toArray();
        if ($this->noMatchQuery !== null) {
            if (\is_a($this->noMatchQuery, BuilderInterface::class, false)) {
                $output['no_match_query'] = $this->noMatchQuery->toArray();
            } else {
                $output['no_match_query'] = $this->noMatchQuery;
            }
        }

        return [$this->getType() => $output];
    }
}
