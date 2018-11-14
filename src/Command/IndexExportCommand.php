<?php

namespace Vairogs\Utils\Search\Command;

use Vairogs\Utils\Search\Service\ExportService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class IndexExportCommand extends AbstractManagerAwareCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->setName('vairogs:search:index:export')->setDescription('Exports data from elasticsearch index.')->addArgument('filename', InputArgument::REQUIRED, 'Select file to store output')->addOption('types', null, InputOption::VALUE_REQUIRED + InputOption::VALUE_IS_ARRAY, 'Export specific types only')->addOption('chunk', null, InputOption::VALUE_REQUIRED, 'Chunk size to use in scan api', 500)->addOption('split', null, InputOption::VALUE_REQUIRED, 'Split file in a separate parts if line number exceeds provided value', 300000);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $manager = $this->getManager($input->getOption('manager'));
        $exportService = $this->getContainer()->get('vairogs.utils.search.export');
        /** @var ExportService $exportService */
        $exportService->exportIndex($manager, $input->getArgument('filename'), $input->getOption('types'), $input->getOption('chunk'), $output, $input->getOption('split'));
        $io->success('Data export completed!');
    }
}
