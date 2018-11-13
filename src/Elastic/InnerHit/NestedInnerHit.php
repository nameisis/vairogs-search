<?php

namespace Vairogs\Utils\Search\Elastic\InnerHit;

use Vairogs\Utils\Search\Elastic\Component\BuilderInterface;
use Vairogs\Utils\Search\Elastic\Component\NameAwareTrait;
use Vairogs\Utils\Search\Elastic\Component\ParametersTrait;
use Vairogs\Utils\Search\Elastic\Search;
use stdClass;

/**
 * @link https://goo.gl/rpnc2e
 */
class NestedInnerHit implements BuilderInterface
{
    use ParametersTrait;
    use NameAwareTrait;

    /**
     * @var string
     */
    private $path;

    /**
     * @var Search
     */
    private $search;

    /**
     * @param string $name
     * @param string $path
     * @param Search $search
     */
    public function __construct($name, $path, Search $search = null)
    {
        $this->setName($name);
        $this->setPath($path);
        if ($search) {
            $this->setSearch($search);
        }
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setPath($path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return Search
     */
    public function getSearch(): Search
    {
        return $this->search;
    }

    /**
     * @param Search $search
     *
     * @return $this
     */
    public function setSearch(Search $search): self
    {
        $this->search = $search;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'nested';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $out = $this->getSearch() ? $this->getSearch()->toArray() : new stdClass();
        $out = [
            $this->getPathType() => [
                $this->getPath() => $out,
            ],
        ];

        return $out;
    }

    /**
     * @return null|string
     */
    private function getPathType(): ?string
    {
        switch ($this->getType()) {
            case 'nested':
                $type = 'path';
                break;
            case 'parent':
                $type = 'type';
                break;
            default:
                $type = null;
        }

        return $type;
    }
}
