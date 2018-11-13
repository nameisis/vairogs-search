<?php

namespace Vairogs\Utils\Search\Elastic\Component;

use Vairogs\Utils\Search\Elastic\Endpoint\EndpointInterface;
use Vairogs\Utils\Search\Elastic\Serializer\OrderedSerializer;

trait SearchTrait
{
    /**
     * @var int
     */
    private $from;

    /**
     * @var int
     */
    private $size;

    /**
     * @var bool
     */
    private $source;

    /**
     * @var array
     */
    private $storedFields;

    /**
     * @var array
     */
    private $scriptFields;

    /**
     * @var array
     */
    private $docValueFields;

    /**
     * @var bool
     */
    private $explain;

    /**
     * @var bool
     */
    private $version;

    /**
     * @var array
     */
    private $indicesBoost;

    /**
     * @var int
     */
    private $minScore;

    /**
     * @var array
     */
    private $searchAfter;

    /**
     * @link https://goo.gl/VY4T6E
     *
     * @var array
     */
    private $uriParams = [];

    /**
     * @var string
     */
    private $scroll;

    /**
     * @var OrderedSerializer
     */
    private $serializer;

    /**
     * @var EndpointInterface[]
     */
    private $endpoints = [];

    /**
     * @return int
     */
    public function getFrom(): int
    {
        return $this->from;
    }

    /**
     * @param int $from
     *
     * @return $this
     */
    public function setFrom($from): self
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     *
     * @return $this
     */
    public function setSize($size): self
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSource(): bool
    {
        return $this->source;
    }

    /**
     * @param bool $source
     *
     * @return $this
     */
    public function setSource($source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return array
     */
    public function getStoredFields(): array
    {
        return $this->storedFields;
    }

    /**
     * @param array $storedFields
     *
     * @return $this
     */
    public function setStoredFields($storedFields): self
    {
        $this->storedFields = $storedFields;

        return $this;
    }

    /**
     * @return array
     */
    public function getScriptFields(): array
    {
        return $this->scriptFields;
    }

    /**
     * @param array $scriptFields
     *
     * @return $this
     */
    public function setScriptFields($scriptFields): self
    {
        $this->scriptFields = $scriptFields;

        return $this;
    }

    /**
     * @return array
     */
    public function getDocValueFields(): array
    {
        return $this->docValueFields;
    }

    /**
     * @param array $docValueFields
     *
     * @return $this
     */
    public function setDocValueFields($docValueFields): self
    {
        $this->docValueFields = $docValueFields;

        return $this;
    }

    /**
     * @return bool
     */
    public function isExplain(): bool
    {
        return $this->explain;
    }

    /**
     * @param bool $explain
     *
     * @return $this
     */
    public function setExplain($explain): self
    {
        $this->explain = $explain;

        return $this;
    }

    /**
     * @return bool
     */
    public function isVersion(): bool
    {
        return $this->version;
    }

    /**
     * @param bool $version
     *
     * @return $this
     */
    public function setVersion($version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return array
     */
    public function getIndicesBoost(): array
    {
        return $this->indicesBoost;
    }

    /**
     * @param array $indicesBoost
     *
     * @return $this
     */
    public function setIndicesBoost($indicesBoost): self
    {
        $this->indicesBoost = $indicesBoost;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinScore(): int
    {
        return $this->minScore;
    }

    /**
     * @param int $minScore
     *
     * @return $this
     */
    public function setMinScore($minScore): self
    {
        $this->minScore = $minScore;

        return $this;
    }

    /**
     * @return array
     */
    public function getSearchAfter(): array
    {
        return $this->searchAfter;
    }

    /**
     * @param array $searchAfter
     *
     * @return $this
     */
    public function setSearchAfter($searchAfter): self
    {
        $this->searchAfter = $searchAfter;

        return $this;
    }

    /**
     * @return string
     */
    public function getScroll(): string
    {
        return $this->scroll;
    }
}
