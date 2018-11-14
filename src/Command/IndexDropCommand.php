<?php

namespace Vairogs\Utils\Search\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class IndexDropCommand extends AbstractManagerAwareCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->setName('vairogs:search:index:drop')->setDescription('Drops elasticsearch index.')->addOption('force', 'f', InputOption::VALUE_NONE, 'Set this parameter to execute this command');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        if ($input->getOption('force')) {
            $this->getManager($input->getOption('manager'))->dropIndex();
            $io->text(\sprintf('Dropped index for the <comment>`%s`</comment> manager', $input->getOption('manager')));
        } else {
            $io->error('ATTENTION:');
            $io->text('This action should not be used in the production environment.');
            $io->error('Option --force is mandatory to drop type(s).');
        }
    }
}
