<?php

namespace AssetManager\Service;

use AssetManager\Resolver\MapResolver;
use Psr\Container\ContainerInterface;

class MapResolverServiceFactory
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $map    = array();

        if (isset($config['asset_manager']['resolver_configs']['map'])) {
            $map = $config['asset_manager']['resolver_configs']['map'];
        }

        $patchStackResolver = new MapResolver($map);

        return $patchStackResolver;
    }
}
