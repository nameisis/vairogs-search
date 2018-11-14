<?php

namespace Vairogs\Utils\Search\Command;

use Vairogs\Utils\Search\Service\IndexSuffixFinder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class IndexCreateCommand extends AbstractManagerAwareCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->setName('vairogs:search:index:create')->setDescription('Creates elasticsearch index.')->addOption('time', 't', InputOption::VALUE_NONE, 'Adds date suffix to the new index name')->addOption('alias', 'a', InputOption::VALUE_NONE, 'If the time suffix is used, its nice to create an alias to the configured index name.')->addOption('no-mapping', null, InputOption::VALUE_NONE, 'Do not include mapping')->addOption('if-not-exists', null, InputOption::VALUE_NONE, 'Don\'t trigger an error, when the index already exists')->addOption('dump', null, InputOption::VALUE_NONE, 'Prints out index mapping json');
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $io = new SymfonyStyle($input, $output);
        $manager = $this->getManager($input->getOption('manager'));
        $originalIndexName = $manager->getIndexName();
        if ($input->getOption('dump')) {
            $io->note('Index mappings:');
            $io->text(\json_encode($manager->getIndexMappings(), \JSON_PRETTY_PRINT));

            return 0;
        }
        if ($input->getOption('time')) {
            /** @var IndexSuffixFinder $finder */
            $finder = $this->getContainer()->get('vairogs.utils.search.client.index_suffix_finder');
            $finder->setNextFreeIndex($manager);
        }
        if ($input->getOption('if-not-exists') && $manager->indexExists()) {
            $io->note(\sprintf('Index `%s` already exists in `%s` manager.', $manager->getIndexName(), $input->getOption('manager')));

            return 0;
        }
        $manager->createIndex($input->getOption('no-mapping'));
        $io->text(\sprintf('Created `<comment>%s</comment>` index for the `<comment>%s</comment>` manager. ', $manager->getIndexName(), $input->getOption('manager')));
        if ($input->getOption('alias') && $originalIndexName !== $manager->getIndexName()) {
            $params['body'] = [
                'actions' => [
                    [
                        'add' => [
                            'index' => $manager->getIndexName(),
                            'alias' => $originalIndexName,
                        ],
                    ],
                ],
            ];
            $message = 'Created an alias `<comment>'.$originalIndexName.'</comment>` for the `<comment>'.$manager->getIndexName().'</comment>` index. ';
            if ($manager->getClient()->indices()->existsAlias(['name' => $originalIndexName])) {
                $currentAlias = $manager->getClient()->indices()->getAlias([
                    'name' => $originalIndexName,
                ]);
                $indexesToRemoveAliases = \implode(',', \array_keys($currentAlias));
                if (!empty($indexesToRemoveAliases)) {
                    $params['body']['actions'][]['remove'] = [
                        'index' => $indexesToRemoveAliases,
                        'alias' => $originalIndexName,
                    ];
                    $message .= 'Removed `<comment>'.$originalIndexName.'</comment>` alias from `<comment>'.$indexesToRemoveAliases.'</comment>` index(es).';
                }
            }
            $manager->getClient()->indices()->updateAliases($params);
            $io->text($message);
        }

        return 0;
    }
}
