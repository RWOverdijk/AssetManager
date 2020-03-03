<?php

namespace AssetManager\Service;

use AssetManager\Resolver\CollectionResolver;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class CollectionResolverServiceFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config      = $container->get('config');
        $collections = array();

        if (isset($config['asset_manager']['resolver_configs']['collections'])) {
            $collections = $config['asset_manager']['resolver_configs']['collections'];
        }

        $collectionResolver = new CollectionResolver($collections);

        return $collectionResolver;
    }

    /**
     * {@inheritDoc}
     *
     * @return CollectionResolver
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, CollectionResolver::class);
    }
}
