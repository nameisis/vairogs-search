<?php

namespace Vairogs\Utils\Search\Result;

use Countable;
use Iterator;
use Vairogs\Utils\Search\Result\Aggregation\AggregationValue;
use Vairogs\Utils\Search\Service\Manager;
use LogicException;
use stdClass;

abstract class AbstractResultsIterator implements Countable, Iterator
{
    /**
     * @var array
     */
    protected $documents = [];
    /**
     * @var array
     */
    private $raw;
    /**
     * @var int
     */
    private $count = 0;

    /**
     * @var array
     */
    private $aggregations = [];

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var array
     */
    private $managerConfig;

    /**
     * @var string
     */
    private $scrollId;

    /**
     * @var string
     */
    private $scrollDuration;

    /**
     * @var int
     */
    private $key = 0;

    /**
     * @param array $rawData
     * @param Manager $manager
     * @param array $scroll
     */
    public function __construct(array $rawData, Manager $manager, array $scroll = [])
    {
        $this->raw = $rawData;
        $this->manager = $manager;
        $this->converter = $manager->getConverter();
        $this->managerConfig = $manager->getConfig();
        if (isset($scroll['_scroll_id'], $scroll['duration'])) {
            $this->scrollId = $scroll['_scroll_id'];
            $this->scrollDuration = $scroll['duration'];
        }
        if (isset($rawData['aggregations'])) {
            $this->aggregations = $rawData['aggregations'];
        }
        if (isset($rawData['hits']['hits'])) {
            $this->documents = $rawData['hits']['hits'];
        }
        if (isset($rawData['hits']['total'])) {
            $this->count = $rawData['hits']['total'];
        }
    }

    public function __destruct()
    {
        if ($this->isScrollable()) {
            $this->manager->clearScroll($this->scrollId);
        }
    }

    /**
     * @return bool
     */
    public function isScrollable(): bool
    {
        return !empty($this->scrollId);
    }

    /**
     * @return array
     */
    public function getRaw(): array
    {
        return $this->raw;
    }

    /**
     * @return array
     */
    public function getAggregations(): array
    {
        return $this->aggregations;
    }

    /**
     * @param string $name
     *
     * @return array|AggregationValue
     */
    public function getAggregation($name)
    {
        return $this->aggregations[$name] ?? null;
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return $this->getDocument($this->key());
    }

    /**
     * @param mixed $key
     *
     * @return mixed
     */
    protected function getDocument($key)
    {
        if (!$this->documentExists($key)) {
            return null;
        }

        return $this->convertDocument($this->documents[$key]);
    }

    /**
     * @param mixed $key
     *
     * @return bool
     */
    protected function documentExists($key): bool
    {
        return \array_key_exists($key, $this->documents);
    }

    /**
     * @param array $document
     *
     * @return stdClass|array
     */
    abstract protected function convertDocument(array $document);

    /**
     * @return mixed
     */
    public function key()
    {
        return $this->key;
    }

    public function next(): void
    {
        $this->advanceKey();
    }

    /**
     * @return $this
     */
    protected function advanceKey(): self
    {
        if ($this->isScrollable() && ($this->documents[$this->key()] === \end($this->documents))) {
            $this->page();
        } else {
            $this->key++;
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function page(): self
    {
        if (!$this->isScrollable() || $this->key() === $this->count()) {
            return $this;
        }
        $raw = $this->manager->getClient()->scroll([
            'scroll' => $this->scrollDuration,
            'scroll_id' => $this->scrollId,
        ]);
        $this->rewind();
        $this->scrollId = $raw['_scroll_id'];
        $this->documents = $raw['hits']['hits'];

        return $this;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }

    public function rewind(): void
    {
        $this->key = 0;
    }

    /**
     * @return mixed|null
     */
    public function first()
    {
        $this->rewind();

        return $this->getDocument($this->key());
    }

    /**
     * @return int
     */
    public function getDocumentScore(): int
    {
        if (!$this->valid()) {
            throw new LogicException('Document score is available only while iterating over results.');
        }

        return $this->documents[$this->key]['_score'] ?? null;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        if ($this->documents === null) {
            return false;
        }
        $valid = $this->documentExists($this->key());
        if ($valid) {
            return true;
        }
        $this->page();

        return $this->documentExists($this->key());
    }

    /**
     * @return mixed
     */
    public function getDocumentSort()
    {
        if (!$this->valid()) {
            throw new LogicException('Document sort is available only while iterating over results.');
        }

        return $this->documents[$this->key]['sort'][0] ?? null;
    }

    /**
     * @return Manager
     */
    protected function getManager(): Manager
    {
        return $this->manager;
    }

    /**
     * @return array
     */
    protected function getManagerConfig(): array
    {
        return $this->managerConfig;
    }

    /**
     * @return Converter
     */
    protected function getConverter(): Converter
    {
        return $this->converter;
    }
}
