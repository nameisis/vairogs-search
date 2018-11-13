<?php

namespace Vairogs\Utils\Search\EventListener;

use Exception;
use Vairogs\Utils\Search\Exception\BulkWithErrorsException;
use Vairogs\Utils\Search\Service\Manager;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class TerminateListener
{
    use ContainerAwareTrait;

    /**
     * @var array
     */
    private $managers;

    /**
     * @param array $managers
     */
    public function __construct(array $managers = [])
    {
        $this->managers = $managers;
    }

    /**
     * @throws BulkWithErrorsException
     */
    public function onKernelTerminate(): void
    {
        foreach ($this->managers as $key => $value) {
            if ($value['force_commit']) {
                try {
                    $manager = $this->container->get(\sprintf('vairogs.utils.search.manager.%s', $key));
                    /** @var Manager $manager */
                } catch (Exception $e) {
                    continue;
                }
                $manager->commit();
            }
        }
    }
}
