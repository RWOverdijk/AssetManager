<?php

namespace AssetManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use AssetManager\Resolver\MapResolver;

class MapResolverServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return MapResolver
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');

        if (!isset($config['asset_manager']['map'])) {
            $config['asset_manager']['map'] = array();
        }


        $patchStackResolver = new MapResolver($config['asset_manager']['map']);

        return $patchStackResolver;
    }
}
