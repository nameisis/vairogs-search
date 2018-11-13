<?php

namespace Vairogs\Utils\Search\Elastic\Query\Specialized;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;

/**
 * @link https://goo.gl/vEApwf
 */
class MoreLikeThisQuery implements BuilderInterface
{
    use ParametersTrait;

    /**
     * @var string
     */
    private $like;

    /**
     * @param string $like
     * @param array $parameters
     */
    public function __construct($like, array $parameters = [])
    {
        $this->like = $like;
        $this->setParameters($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'more_like_this';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $query = [];
        if ($this->hasParameter('ids') || $this->hasParameter('docs')) {
            $query['like'] = $this->like;
        }
        $output = $this->processArray($query);

        return [$this->getType() => $output];
    }
}
