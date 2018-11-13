<?php

namespace Vairogs\Utils\Search\Elastic\Endpoint;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;

interface EndpointInterface extends NormalizableInterface
{
    /**
     * @param BuilderInterface $builder
     * @param array|string|int|null $key
     *
     * @return string
     */
    public function add(BuilderInterface $builder, $key = null): string;

    /**
     * @param BuilderInterface $builder
     * @param array|string $boolType
     * @param array|string|int|null $key
     *
     * @return string
     */
    public function addToBool(BuilderInterface $builder, $boolType = null, $key = null): string;

    /**
     * @param int|string $key
     *
     * @return $this
     */
    public function remove($key): EndpointInterface;

    /**
     * @param int|string $key
     *
     * @return BuilderInterface|null
     */
    public function get($key): ?BuilderInterface;

    /**
     * @param string|null $boolType
     *
     * @return array
     */
    public function getAll($boolType = null): array;

    /**
     * @return BuilderInterface
     */
    public function getBool(): BuilderInterface;
}
