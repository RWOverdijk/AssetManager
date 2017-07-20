<?php

namespace AssetManager\Service;

use AssetManager\Resolver\AliasPathStackResolver;
use Psr\Container\ContainerInterface;

class AliasPathStackResolverServiceFactory
{
    /**
     * @inheritDoc
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
