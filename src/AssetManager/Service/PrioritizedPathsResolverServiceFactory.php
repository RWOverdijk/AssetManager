<?php

namespace AssetManager\Service;

use Interop\Container\ContainerInterface;
use AssetManager\Resolver\PrioritizedPathsResolver;

class PrioritizedPathsResolverServiceFactory
{
    /**
     * Build A Map Resolver
     *
     * @param ContainerInterface $container Container Service
     *
     * @return PrioritizedPathsResolver
     */
    public function __invoke(ContainerInterface $container)
    {
        $config                   = $container->get('config');
        $prioritizedPathsResolver = new PrioritizedPathsResolver();
        $paths                    = isset($config['asset_manager']['resolver_configs']['prioritized_paths'])
            ? $config['asset_manager']['resolver_configs']['prioritized_paths']
            : array();
        $prioritizedPathsResolver->addPaths($paths);

        return $prioritizedPathsResolver;
    }
}
