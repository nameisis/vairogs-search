<?php

namespace Vairogs\Utils\Search\Elastic\Endpoint;

use BadFunctionCallException;
use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;
use Vairogs\Utils\Search\Elastic\Serializer\Normalizer\AbstractNormalizable;
use OverflowException;

abstract class AbstractEndpoint extends AbstractNormalizable implements EndpointInterface
{
    use ParametersTrait;

    /**
     * @var BuilderInterface[]
     */
    private $container = [];

    /**
     * {@inheritdoc}
     */
    public function add(BuilderInterface $builder, $key = null): string
    {
        if (\array_key_exists($key, $this->container)) {
            throw new OverflowException(\sprintf('Builder with %s name for endpoint has already been added!', $key));
        }
        if (!$key) {
            $key = \bin2hex(\random_bytes(30));
        }
        $this->container[$key] = $builder;

        return $key;
    }

    /**
     * {@inheritdoc}
     */
    public function addToBool(BuilderInterface $builder, $boolType = null, $key = null): string
    {
        throw new BadFunctionCallException(\sprintf("Endpoint %s doesn't support bool statements", __CLASS__));
    }

    /**
     * {@inheritdoc}
     */
    public function get($key): BuilderInterface
    {
        if ($this->has($key)) {
            return $this->container[$key];
        }

        return null;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key): bool
    {
        return \array_key_exists($key, $this->container);
    }

    /**
     * {@inheritdoc}
     */
    public function getAll($boolType = null): array
    {
        return $this->container;
    }

    /**
     * {@inheritdoc}
     */
    public function getBool(): BuilderInterface
    {
        throw new BadFunctionCallException(\sprintf("Endpoint %s doesn't support bool statements", __CLASS__));
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key): EndpointInterface
    {
        if ($this->has($key)) {
            unset($this->container[$key]);
        }

        return $this;
    }
}
