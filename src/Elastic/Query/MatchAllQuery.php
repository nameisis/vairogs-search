<?php

namespace Vairogs\Utils\Search\Elastic\Query;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;
use stdClass;

/**
 * @link https://goo.gl/idNwWf
 */
class MatchAllQuery implements BuilderInterface
{
    use ParametersTrait;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->setParameters($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'match_all';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $params = $this->getParameters();

        return [$this->getType() => !empty($params) ? $params : new stdClass()];
    }
}
