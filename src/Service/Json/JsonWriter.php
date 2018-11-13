<?php

namespace Vairogs\Utils\Search\Service\Json;

use OverflowException;

class JsonWriter
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
     * @var array
     */
    private $metadata;

    /**
     * @var int
     */
    private $currentPosition = 0;

    /**
     * @param string $filename
     * @param array $metadata
     */
    public function __construct($filename, array $metadata = [])
    {
        $this->filename = $filename;
        $this->metadata = $metadata;
    }

    public function __destruct()
    {
        $this->finalize();
    }

    public function finalize(): void
    {
        $this->initialize();
        if (\is_resource($this->handle)) {
            \fwrite($this->handle, "\n]");
            \fclose($this->handle);
        }
    }

    protected function initialize(): void
    {
        if ($this->handle !== null) {
            return;
        }
        $this->handle = \fopen($this->filename, 'wb');
        if (!$this->handle) {
            return;
        }
        \fwrite($this->handle, "[\n");
        \fwrite($this->handle, \json_encode($this->metadata));
    }

    /**
     * @param mixed $document
     *
     * @throws OverflowException
     */
    public function push($document): void
    {
        $this->initialize();
        $this->currentPosition++;
        if (isset($this->metadata['count']) && $this->currentPosition > $this->metadata['count']) {
            throw new OverflowException(\sprintf('This writer was set up to write %d documents, got more.', $this->metadata['count']));
        }
        \fwrite($this->handle, ",\n");
        \fwrite($this->handle, \json_encode($document));
        if (isset($this->metadata['count']) && $this->currentPosition === $this->metadata['count']) {
            $this->finalize();
        }
    }
}
