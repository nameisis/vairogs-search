<?php

namespace Vairogs\Utils\Search\Elastic\Query\Compound;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;
use UnexpectedValueException;

/**
 * @link https://goo.gl/EPzukU
 */
class BoolQuery implements BuilderInterface
{
    use ParametersTrait;

    public const MUST = 'must';
    public const MUST_NOT = 'must_not';
    public const SHOULD = 'should';
    public const FILTER = 'filter';

    /**
     * @var array
     */
    private $container = [];

    public function __construct()
    {

    }

    /**
     * @param  string|null $boolType
     *
     * @return array
     */
    public function getQueries($boolType = null): array
    {
        if ($boolType === null) {
            $queries = [[]];
            foreach ($this->container as $item) {
                $queries[] = $item;
            }

            return \array_merge(...$queries);
        }

        return $this->container[$boolType] ?? [];
    }

    /**
     * @param BuilderInterface $query
     * @param string $type
     * @param string $key
     *
     * @return string
     *
     * @throws UnexpectedValueException
     */
    public function add(BuilderInterface $query, $type = self::MUST, $key = null): string
    {
        if (!\in_array($type, [self::MUST, self::MUST_NOT, self::SHOULD, self::FILTER], true)) {
            throw new UnexpectedValueException(\sprintf('The bool operator %s is not supported', $type));
        }
        if (!$key) {
            $key = \bin2hex(\random_bytes(30));
        }
        $this->container[$type][$key] = $query;

        return $key;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        if (\count($this->container) === 1 && isset($this->container[self::MUST]) && \count($this->container[self::MUST]) === 1) {
            $query = \reset($this->container[self::MUST]);

            return $query->toArray();
        }
        $output = [];
        foreach ($this->container as $boolType => $builders) {
            foreach ($builders as $builder) {
                /** @var BuilderInterface $builder */
                $output[$boolType][] = $builder->toArray();
            }
        }
        $output = $this->processArray($output);

        return [$this->getType() => $output];
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'bool';
    }
}
