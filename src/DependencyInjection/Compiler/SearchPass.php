<?php

namespace Vairogs\Utils\Search\DependencyInjection\Compiler;

use Vairogs\Utils\DependencyInjection\Component\Definable;
use Vairogs\Utils\VairogsBundle;
use Vairogs\Utils\Search\Service\Manager;
use Vairogs\Utils\Search\Service\Repository;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class SearchPass implements CompilerPassInterface
{
    public const NAME = VairogsBundle::FULL_ALIAS.'.'.Definable::SEARCH;

    public function process(ContainerBuilder $container): void
    {
        if (VairogsBundle::isEnabled($container, Definable::SEARCH)) {
            $analysis = $container->getParameter(\sprintf('%s.analysis', self::NAME));
            $managers = $container->getParameter(\sprintf('%s.managers', self::NAME));
            $collector = $container->get(\sprintf('%s.metadata_collector', self::NAME));
            foreach ($managers as $managerName => $manager) {
                $connection = $manager['index'];
                $managerName = \strtolower($managerName);
                $managerDefinition = new Definition(Manager::class, [
                    $managerName,
                    $connection,
                    $analysis,
                    $manager,
                ]);
                $managerDefinition->setFactory([
                    new Reference(\sprintf('%s.manager_factory', self::NAME)),
                    'createManager',
                ]);
                $container->setDefinition(\sprintf('%s.manager.%s', self::NAME, $managerName), $managerDefinition)->setPublic(true);
                if ($managerName === 'default') {
                    $container->setAlias(\sprintf('%s.manager', self::NAME), \sprintf('%s.manager.default', self::NAME));
                }

                foreach ($collector->getMappings($manager['mappings']) ?? [] as $repositoryType => $repositoryDetails) {
                    $repositoryDefinition = new Definition(Repository::class, [$repositoryDetails['namespace']]);
                    if ($managerName === 'default' && isset($repositoryDetails['directory_name'])) {
                        $container->get(\sprintf('%s.document_finder', self::NAME))->setDocumentDir($repositoryDetails['directory_name']);
                    }
                    $repositoryDefinition->setFactory([
                        new Reference(\sprintf('%s.manager.%s', self::NAME, $managerName)),
                        'getRepository',
                    ]);
                    $repositoryId = \sprintf('%s.manager.%s.%s', self::NAME, $managerName, $repositoryType);
                    $container->setDefinition($repositoryId, $repositoryDefinition);
                }
            }
        }
    }
}
