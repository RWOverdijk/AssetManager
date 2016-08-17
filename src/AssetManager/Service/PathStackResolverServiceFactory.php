<?php

namespace AssetManager\Service;

use Interop\Container\ContainerInterface;
use AssetManager\Resolver\PathStackResolver;

class PathStackResolverServiceFactory
{
    /**
     * Build A Map Resolver
     *
     * @param ContainerInterface $container Container Service
     *
     * @return PathStackResolver
     */
    public function __invoke(ContainerInterface $container)
    {
        $config            = $container->get('config');
        $pathStackResolver = new PathStackResolver();
        $paths             = array();

        if (isset($config['asset_manager']['resolver_configs']['paths'])) {
            $paths = $config['asset_manager']['resolver_configs']['paths'];
        }

        $pathStackResolver->addPaths($paths);

        return $pathStackResolver;
    }
}
