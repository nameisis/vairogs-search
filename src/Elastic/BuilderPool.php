<?php

namespace Vairogs\Utils\Search\Elastic;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;

class BuilderPool
{
    /**
     * @var BuilderInterface[]
     */
    private $bag = [];

    /**
     * @param BuilderInterface[] $builders
     */
    public function __construct($builders = [])
    {
        foreach ($builders as $builder) {
            $this->add($builder);
        }
    }

    /**
     * @param BuilderInterface $builder
     *
     * @return string
     */
    public function add(BuilderInterface $builder): string
    {
        if (\method_exists($builder, 'getName')) {
            $name = $builder->getName();
        } else {
            $name = \bin2hex(\random_bytes(30));
        }
        $this->bag[$name] = $builder;

        return $name;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has($name): bool
    {
        return isset($this->bag[$name]);
    }

    /**
     * @param string $name Builder name.
     */
    public function remove($name): void
    {
        unset($this->bag[$name]);
    }

    public function clear(): void
    {
        $this->bag = [];
    }

    /**
     * @param string $name Builder name.
     *
     * @return BuilderInterface
     */
    public function get($name): BuilderInterface
    {
        return $this->bag[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $output = [[]];
        foreach ($this->all() as $builder) {
            $output[] = $builder->toArray();
        }

        return \array_merge(...$output);
    }

    /**
     * @param string|null $type
     *
     * @return BuilderInterface[]
     */
    public function all($type = null): array
    {
        return \array_filter($this->bag, function(BuilderInterface $builder) use ($type) {
            return $type === null || $builder->getType() === $type;
        });
    }
}
