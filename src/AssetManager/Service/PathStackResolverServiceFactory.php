<?php

namespace AssetManager\Service;

use AssetManager\Resolver\PathStackResolver;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PathStackResolverServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): PathStackResolver
    {
        $config            = $container->get('config');
        $pathStackResolver = new PathStackResolver();

        $paths = $config['asset_manager']['resolver_configs']['paths'] ?? [];

        $pathStackResolver->addPaths($paths);

        return $pathStackResolver;
    }
}
