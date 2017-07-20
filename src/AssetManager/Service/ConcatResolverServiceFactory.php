<?php

namespace AssetManager\Service;

use AssetManager\Resolver\ConcatResolver;
use Psr\Container\ContainerInterface;

class ConcatResolverServiceFactory
{
    /**
     * @inheritDoc
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
