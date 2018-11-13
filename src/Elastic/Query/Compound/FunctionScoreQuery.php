<?php

namespace Vairogs\Utils\Search\Elastic\Query\Compound;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;
use stdClass;

/**
 * @link https://goo.gl/gXBhqw
 */
class FunctionScoreQuery implements BuilderInterface
{
    use ParametersTrait;

    /**
     * @var BuilderInterface
     */
    private $query;

    /**
     * @var array[]
     */
    private $functions;

    /**
     * @param BuilderInterface $query
     * @param array $parameters
     */
    public function __construct(BuilderInterface $query, array $parameters = [])
    {
        $this->query = $query;
        $this->setParameters($parameters);
    }

    /**
     * @param string $field
     * @param float $factor
     * @param string $modifier
     * @param BuilderInterface $query
     *
     * @return $this
     */
    public function addFieldValueFactorFunction($field, $factor, $modifier = 'none', BuilderInterface $query = null): self
    {
        $function = [
            'field_value_factor' => [
                'field' => $field,
                'factor' => $factor,
                'modifier' => $modifier,
            ],
        ];
        $this->applyFilter($function, $query);
        $this->functions[] = $function;

        return $this;
    }

    /**
     * @param array $function
     * @param BuilderInterface $query
     */
    private function applyFilter(array &$function, BuilderInterface $query = null): void
    {
        if ($query) {
            $function['filter'] = $query->toArray();
        }
    }

    /**
     * @param string $type
     * @param string $field
     * @param array $function
     * @param array $options
     * @param BuilderInterface $query
     * @param int $weight
     *
     * @return $this
     */
    public function addDecayFunction($type, $field, array $function, array $options = [], BuilderInterface $query = null, $weight = null): self
    {
        $function = \array_filter([
            $type => \array_merge([$field => $function], $options),
            'weight' => $weight,
        ]);
        $this->applyFilter($function, $query);
        $this->functions[] = $function;

        return $this;
    }

    /**
     * @param float $weight
     * @param BuilderInterface $query
     *
     * @return $this
     */
    public function addWeightFunction($weight, BuilderInterface $query = null): self
    {
        $function = [
            'weight' => $weight,
        ];
        $this->applyFilter($function, $query);
        $this->functions[] = $function;

        return $this;
    }

    /**
     * @param mixed $seed
     * @param BuilderInterface $query
     *
     * @return $this
     */
    public function addRandomFunction($seed = null, BuilderInterface $query = null): self
    {
        $function = [
            'random_score' => $seed ? ['seed' => $seed] : new stdClass(),
        ];
        $this->applyFilter($function, $query);
        $this->functions[] = $function;

        return $this;
    }

    /**
     * @param string $inline
     * @param array $params
     * @param array $options
     * @param BuilderInterface $query
     *
     * @return $this
     */
    public function addScriptScoreFunction($inline, array $params = [], array $options = [], BuilderInterface $query = null): self
    {
        $function = [
            'script_score' => [
                'script' => \array_filter(\array_merge([
                    'lang' => 'painless',
                    'inline' => $inline,
                    'params' => $params,
                ], $options)),
            ],
        ];
        $this->applyFilter($function, $query);
        $this->functions[] = $function;

        return $this;
    }

    /**
     * @param array $function
     *
     * @return $this
     */
    public function addSimpleFunction(array $function): self
    {
        $this->functions[] = $function;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $query = [
            'query' => $this->query->toArray(),
            'functions' => $this->functions,
        ];
        $output = $this->processArray($query);

        return [$this->getType() => $output];
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'function_score';
    }
}
