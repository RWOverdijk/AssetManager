<?php

namespace AssetManager\Service;

use AssetManager\Resolver\AliasPathStackResolver;
use Interop\Container\ContainerInterface;
use AssetManager\Resolver\PathStackResolver;

class AliasPathStackResolverServiceFactory
{
    /**
     * Build the Alias Path Stack Reolver
     * 
     * @param ContainerInterface $container Container Service
     *
     * @return PathStackResolver
     */
    public function __invoke(ContainerInterface $container)
    {
        $config  = $container->get('config');
        $aliases = array();

        if (isset($config['asset_manager']['resolver_configs']['aliases'])) {
            $aliases = $config['asset_manager']['resolver_configs']['aliases'];
        }

        return new AliasPathStackResolver($aliases);
    }
}
