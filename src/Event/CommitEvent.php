<?php

namespace Vairogs\Utils\Search\Event;

use Symfony\Component\EventDispatcher\Event;

class CommitEvent extends Event
{
    /**
     * @var string
     */
    private $commitMode;

    /**
     * @var array
     */
    private $bulkParams;

    /**
     * @param string $commitMode
     * @param array|null $bulkParams
     */
    public function __construct($commitMode, $bulkParams = [])
    {
        $this->commitMode = $commitMode;
        $this->bulkParams = $bulkParams;
    }

    /**
     * @return string
     */
    public function getCommitMode(): string
    {
        return $this->commitMode;
    }

    /**
     * @param string $commitMode
     */
    public function setCommitMode($commitMode): void
    {
        $this->commitMode = $commitMode;
    }

    /**
     * @return array
     */
    public function getBulkParams(): array
    {
        return $this->bulkParams;
    }

    /**
     * @param array $bulkParams
     */
    public function setBulkParams($bulkParams): void
    {
        $this->bulkParams = $bulkParams;
    }
}
