<?php

namespace Vairogs\Utils\Search\Elastic\Query\TermLevel;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;

/**
 * @link https://goo.gl/CeJ57M
 */
class PrefixQuery implements BuilderInterface
{
    use ParametersTrait;

    /**
     * @var string
     */
    protected $field;

    /**
     * @var string
     */
    protected $value;

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
        return 'prefix';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $query = [
            'value' => $this->value,
        ];
        $output = [
            $this->field => $this->processArray($query),
        ];

        return [$this->getType() => $output];
    }
}
