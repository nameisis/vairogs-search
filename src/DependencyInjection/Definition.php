<?php

namespace Vairogs\Utils\Search\DependencyInjection;

use Vairogs\Utils\DependencyInjection\Component\Definable;
use Psr\Log\LogLevel;
use ReflectionClass;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class Definition implements Definable
{
    private const ALLOWED = [
        Definable::SEARCH,
    ];

    public function getExtensionDefinition($extension): ArrayNodeDefinition
    {
        if (!\in_array($extension, self::ALLOWED, true)) {
            throw new InvalidConfigurationException(\sprintf('Invalid extension: %s', $extension));
        }

        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root(Definable::SEARCH);
        /** @var ArrayNodeDefinition $node */

        // @formatter:off
        $node
            ->canBeEnabled()
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('cache')
                    ->defaultValue(true)
                ->end()
                ->booleanNode('profiler')
                    ->defaultValue(true)
                ->end()
                ->append($this->getAnalysisNode())
                ->append($this->getManagersNode())
            ->end();
        // @formatter:on

        return $node;
    }

    private function getAnalysisNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('analysis');

        // @formatter:off
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('tokenizer')
                    ->defaultValue([])
                    ->variablePrototype()
                    ->end()
                ->end()
                ->arrayNode('filter')
                    ->defaultValue([])
                    ->variablePrototype()
                    ->end()
                ->end()
                ->arrayNode('analyzer')
                    ->defaultValue([])
                    ->variablePrototype()
                    ->end()
                ->end()
                ->arrayNode('char_filter')
                    ->defaultValue([])
                    ->variablePrototype()
                    ->end()
                ->end()
            ->end();
        // @formatter:on

        return $node;
    }

    private function getManagersNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('managers');

        // @formatter:off
        $node
            ->isRequired()
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->children()
                    ->arrayNode('index')
                        ->children()
                            ->scalarNode('index_name')
                                ->isRequired()
                            ->end()
                            ->arrayNode('hosts')
                                ->defaultValue([])
                                ->scalarPrototype()
                                ->end()
                            ->end()
                            ->arrayNode('settings')
                                ->defaultValue(
                                    [
                                        'number_of_replicas' => 0,
                                        'number_of_shards' => 1,
                                        'refresh_interval' => -1,
                                    ]
                                )
                                ->variablePrototype()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->integerNode('bulk_size')
                        ->min(0)
                        ->defaultValue(100)
                    ->end()
                    ->enumNode('commit_mode')
                        ->values(['refresh', 'flush', 'none'])
                        ->defaultValue('refresh')
                    ->end()
                    ->arrayNode('logger')
                        ->addDefaultsIfNotSet()
                        ->beforeNormalization()
                            ->ifTrue(
                                function ($v) {
                                    return \is_bool($v);
                                }
                            )
                            ->then(
                                function ($v) {
                                    return ['enabled' => $v];
                                }
                            )
                        ->end()
                        ->children()
                            ->booleanNode('enabled')
                                ->defaultValue(false)
                            ->end()
                            ->scalarNode('level')
                                ->defaultValue(LogLevel::WARNING)
                                ->validate()
                                    ->ifNotInArray((new ReflectionClass(LogLevel::class))->getConstants())
                                    ->thenInvalid('Invalid PSR log level.')
                                ->end()
                            ->end()
                            ->scalarNode('log_file_name')
                                ->defaultValue(null)
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('mappings')
                        ->variablePrototype()
                        ->end()
                    ->end()
                    ->booleanNode('force_commit')
                        ->defaultValue(true)
                    ->end()
                ->end()
            ->end();
        // @formatter:on

        return $node;
    }
}
