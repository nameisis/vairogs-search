<?php

namespace Vairogs\Utils\Search\Command;

use Vairogs\Utils\Search\Service\ImportService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class IndexImportCommand extends AbstractManagerAwareCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->setName('vairogs:search:index:import')->setDescription('Imports data to elasticsearch index.')->addArgument('filename', InputArgument::REQUIRED, 'Select file to store output')->addOption('bulk-size', 'b', InputOption::VALUE_REQUIRED, 'Set bulk size for import', 1000)->addOption('gzip', 'z', InputOption::VALUE_NONE, 'Import a gzip file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $manager = $this->getManager($input->getOption('manager'));
        $options = [];
        if ($input->getOption('gzip')) {
            $options['gzip'] = null;
        }
        $options['bulk-size'] = $input->getOption('bulk-size');
        $importService = $this->getContainer()->get('vairogs.utils.search.import');
        /** @var ImportService $importService */
        $importService->importIndex($manager, $input->getArgument('filename'), $output, $options);
        $io->success('Data import completed!');
    }
}
