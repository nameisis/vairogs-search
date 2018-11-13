<?php

namespace Vairogs\Utils\Search\Elastic\Query\TermLevel;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;

/**
 * @link https://goo.gl/hhMc1G
 */
class TermQuery implements BuilderInterface
{
    use ParametersTrait;

    /**
     * @var string
     */
    private $field;

    /**
     * @var string
     */
    private $value;

    /**
     * @param string $field
     * @param string $value
     * @param array $parameters
     */
    public function __construct($field, $value, array $parameters = [])
    {
        $this->field = $field;
        $this->value = $value;
        $this->setParameters($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'term';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $query = $this->processArray();
        if (empty($query)) {
            $query = $this->value;
        } else {
            $query['value'] = $this->value;
        }
        $output = [
            $this->field => $query,
        ];

        return [$this->getType() => $output];
    }
}
