<?php

namespace AssetManager\Service;

use Interop\Container\ContainerInterface;
use AssetManager\Resolver\ConcatResolver;

class ConcatResolverServiceFactory
{
    /**
     * Build A Concat Resolver
     *
     * @param ContainerInterface $container Container Service
     *
     * @return ConcatResolver
     */
    public function __invoke(ContainerInterface $container)
    {
        $config      = $container->get('config');
        $files = array();

        if (isset($config['asset_manager']['resolver_configs']['concat'])) {
            $files = $config['asset_manager']['resolver_configs']['concat'];
        }

        $concatResolver = new ConcatResolver($files);

        return $concatResolver;
    }
}
