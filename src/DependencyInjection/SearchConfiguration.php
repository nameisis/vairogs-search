<?php

namespace Vairogs\Utils\Search\DependencyInjection;

use Vairogs\Utils\DependencyInjection\Component\Configurable;
use Vairogs\Utils\DependencyInjection\Component\Definable;
use Vairogs\Utils\Search\Service\ManagerFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class SearchConfiguration implements Configurable
{
    protected $alias;

    public function __construct($alias)
    {
        $this->alias = $alias.'.'.Definable::SEARCH;
    }

    public function configure(ContainerBuilder $container): void
    {
        // @formatter:off
        $definition = new Definition(ManagerFactory::class,
            [
                new Reference(\sprintf('%s.metadata_collector', $this->alias)),
                new Reference(\sprintf('%s.result_converter', $this->alias)),
                new Reference(\sprintf('%s.tracer', $this->alias))
            ]
        );
        // @formatter:on
        $definition->addMethodCall('setEventDispatcher', [new Reference('event_dispatcher')]);
        // @formatter:off
        $definition->addMethodCall('setStopwatch',
            [
                new Reference('debug.stopwatch', ContainerBuilder::NULL_ON_INVALID_REFERENCE)
            ]
        );
        // @formatter:on
        $container->setDefinition(\sprintf('%s.manager_factory', $this->alias), $definition);
    }
}
