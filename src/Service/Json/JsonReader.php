<?php

namespace Vairogs\Utils\Search\Service\Json;

use Countable;
use InvalidArgumentException;
use Iterator;
use Vairogs\Utils\Search\Service\Manager;
use LogicException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JsonReader implements Countable, Iterator
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var resource
     */
    private $handle;

    /**
     * @var int
     */
    private $key = 0;

    /**
     * @var string
     */
    private $currentLine;

    /**
     * @var mixed
     */
    private $metadata;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    /**
     * @var array
     */
    private $options;

    /**
     * @param Manager $manager
     * @param string $filename
     * @param array $options
     *
     */
    public function __construct($manager, $filename, $options)
    {
        $this->manager = $manager;
        $this->filename = $filename;
        $this->options = $options;
    }

    public function __destruct()
    {
        if ($this->handle !== null) {
            \fclose($this->handle);
        }
    }

    /**
     * @return Manager
     */
    public function getManager(): Manager
    {
        return $this->manager;
    }

    /**
     * @return mixed
     */
    public function current()
    {
        if ($this->currentLine === null) {
            $this->readLine();
        }

        return $this->currentLine;
    }

    protected function readLine()
    {
        $buffer = '';
        while ($buffer === '') {
            $buffer = \fgets($this->getFileHandler());
            if ($buffer === false) {
                $this->currentLine = null;

                return;
            }
            $buffer = \trim($buffer);
        }
        if ($buffer === ']') {
            $this->currentLine = null;

            return;
        }
        $data = \json_decode(\rtrim($buffer, ','), true);
        $this->currentLine = $this->getOptionsResolver()->resolve($data);
    }

    /**
     * @return resource
     *
     * @throws LogicException
     */
    protected function getFileHandler()
    {
        if ($this->handle === null) {
            $isGzip = \array_key_exists('gzip', $this->options);
            $filename = !$isGzip ? $this->filename : \sprintf('compress.zlib://%s', $this->filename);
            $fileHandler = \fopen($filename, 'rb');
            if ($fileHandler === false) {
                throw new LogicException('Can not open file.');
            }
            $this->handle = $fileHandler;
        }

        return $this->handle;
    }

    /**
     * @return OptionsResolver
     */
    private function getOptionsResolver(): OptionsResolver
    {
        if (!$this->optionsResolver) {
            $this->optionsResolver = new OptionsResolver();
            $this->configureResolver($this->optionsResolver);
        }

        return $this->optionsResolver;
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureResolver(OptionsResolver $resolver)
    {
        $resolver->setRequired(['_id', '_type', '_source'])->setDefaults(['_score' => null, 'fields' => []])->addAllowedTypes('_id', [
            'integer',
            'string',
        ])->addAllowedTypes('_type', 'string')->addAllowedTypes('_source', 'array')->addAllowedTypes('fields', 'array');
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function next(): void
    {
        $this->readLine();
        $this->key++;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        \rewind($this->getFileHandler());
        $this->metadata = null;
        $this->readMetadata();
        $this->readLine();
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function readMetadata()
    {
        if ($this->metadata !== null) {
            return;
        }
        $line = \fgets($this->getFileHandler());
        if (\trim($line) !== '[') {
            throw new InvalidArgumentException('Given file does not match expected pattern.');
        }
        $line = \trim(\fgets($this->getFileHandler()));
        $this->metadata = \json_decode(\rtrim($line, ','), true);
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        return !\feof($this->getFileHandler()) && $this->currentLine;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $metadata = $this->getMetadata();
        if (!isset($metadata['count'])) {
            throw new LogicException('Given file does not contain count of documents.');
        }

        return $metadata['count'];
    }

    /**
     * @return array|null
     */
    public function getMetadata(): ?array
    {
        $this->readMetadata();

        return $this->metadata;
    }
}
