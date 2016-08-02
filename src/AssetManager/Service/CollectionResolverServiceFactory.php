<?php

namespace AssetManager\Service;

use AssetManager\Resolver\CollectionResolver;
use Interop\Container\ContainerInterface;

class CollectionResolverServiceFactory
{
    /**
     * Build A Collection Resolver
     *
     * @param ContainerInterface $container Container Service
     *
     * @return CollectionResolver
     */
    public function __invoke(ContainerInterface $container)
    {
        $config      = $container->get('Config');
        $collections = array();

        if (isset($config['asset_manager']['resolver_configs']['collections'])) {
            $collections = $config['asset_manager']['resolver_configs']['collections'];
        }

        $collectionResolver = new CollectionResolver($collections);

        return $collectionResolver;
    }
}
