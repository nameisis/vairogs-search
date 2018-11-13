<?php

namespace Vairogs\Utils\Search\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CacheClearCommand extends AbstractManagerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();
        $this->setName('vairogs:search:cache:clear')->setDescription('Clears elasticsearch client cache.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->getManager($input->getOption('manager'))->clearCache();
        $io->success(\sprintf('Elasticsearch index cache has been cleared for manager named `%s`', $input->getOption('manager')));
    }
}
