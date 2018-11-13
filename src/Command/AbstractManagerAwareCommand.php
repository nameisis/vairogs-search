<?php

namespace Vairogs\Utils\Search\Command;

use Vairogs\Utils\Search\Service\Manager;
use RuntimeException;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractManagerAwareCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->addOption('manager', 'm', InputOption::VALUE_REQUIRED, 'Manager name', 'default');
    }

    /**
     * @param string $name
     *
     * @return Manager|stdClass
     *
     * @throws RuntimeException
     */
    protected function getManager($name): Manager
    {
        $id = $this->getManagerId($name);
        if ($this->getContainer()->has($id)) {
            return $this->getContainer()->get($id);
        }
        throw new RuntimeException(\sprintf('Manager named `%s` not found. Available: `%s`.', $name, \implode('`, `', \array_keys($this->getContainer()->getParameter('vairogs.utils.search.managers')))));
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function getManagerId($name): string
    {
        return \sprintf('vairogs.utils.search.manager.%s', $name);
    }
}
