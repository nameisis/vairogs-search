<?php

namespace Vairogs\Utils\Search\Elastic\Query\TermLevel;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;

/**
 * @link https://goo.gl/XKSw2h
 */
class TermsQuery implements BuilderInterface
{
    use ParametersTrait;

    /**
     * @var string
     */
    private $field;

    /**
     * @var array
     */
    private $terms;

    /**
     * Constructor.
     *
     * @param string $field
     * @param array $terms
     * @param array $parameters
     */
    public function __construct($field, $terms, array $parameters = [])
    {
        $this->field = $field;
        $this->terms = $terms;
        $this->setParameters($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'terms';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $query = [
            $this->field => $this->terms,
        ];
        $output = $this->processArray($query);

        return [$this->getType() => $output];
    }
}
