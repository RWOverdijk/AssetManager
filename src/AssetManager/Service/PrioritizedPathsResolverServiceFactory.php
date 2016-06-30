<?php

namespace AssetManager\Service;

use AssetManager\Resolver\PrioritizedPathsResolver;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PrioritizedPathsResolverServiceFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config                   = $container->get('config');
        $prioritizedPathsResolver = new PrioritizedPathsResolver();
        $paths                    = isset($config['asset_manager']['resolver_configs']['prioritized_paths'])
            ? $config['asset_manager']['resolver_configs']['prioritized_paths']
            : array();
        $prioritizedPathsResolver->addPaths($paths);

        return $prioritizedPathsResolver;
    }

    /**
     * {@inheritDoc}
     *
     * @return PrioritizedPathsResolver
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, PrioritizedPathsResolver::class);
    }
}
