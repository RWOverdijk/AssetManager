<?php

namespace AssetManager\Service;

use AssetManager\Resolver\ConcatResolver;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ConcatResolverServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ConcatResolver
    {
        $config = $container->get('config');
        $files  = $config['asset_manager']['resolver_configs']['concat'] ?? [];
        return new ConcatResolver($files);
    }
}
