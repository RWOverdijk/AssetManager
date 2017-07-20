<?php

namespace AssetManager\Service;

use AssetManager\Resolver\CollectionResolver;
use Psr\Container\ContainerInterface;

class CollectionResolverServiceFactory
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container)
    {
        $config      = $container->get('config');
        $collections = array();

        if (isset($config['asset_manager']['resolver_configs']['collections'])) {
            $collections = $config['asset_manager']['resolver_configs']['collections'];
        }

        $collectionResolver = new CollectionResolver($collections);

        return $collectionResolver;
    }
}
