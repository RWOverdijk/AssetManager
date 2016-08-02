<?php

namespace AssetManager\Service;

use Interop\Container\ContainerInterface;
use AssetManager\Resolver\MapResolver;

class MapResolverServiceFactory
{
    /**
     * Build A Map Resolver
     *
     * @param ContainerInterface $container Container Service
     *
     * @return MapResolver
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('Config');
        $map    = array();

        if (isset($config['asset_manager']['resolver_configs']['map'])) {
            $map = $config['asset_manager']['resolver_configs']['map'];
        }

        $patchStackResolver = new MapResolver($map);

        return $patchStackResolver;
    }
}
