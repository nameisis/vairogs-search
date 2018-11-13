<?php

namespace Vairogs\Utils\Search\Event;

use Vairogs\Utils\Search\Service\Manager;
use Symfony\Component\EventDispatcher\Event;

class PostCreateManagerEvent extends Event
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return Manager
     */
    public function getManager(): Manager
    {
        return $this->manager;
    }

    /**
     * @param Manager $manager
     */
    public function setManager($manager): void
    {
        $this->manager = $manager;
    }
}
