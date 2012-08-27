<?php

namespace AssetManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use AssetManager\Resolver\CollectionResolver;

class CollectionResolverServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return CollectionResolver
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        if (isset($config['asset_manager']['resolver_configs']['collections'])) {
            $collections = $config['asset_manager']['resolver_configs']['collections'];
        } else {
            $collections = array();
        }

        $collectionResolver = new CollectionResolver($collections);

        return $collectionResolver;
    }
}
