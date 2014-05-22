<?php

namespace AssetManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for the Asset Cache Manager Service
 *
 * @package AssetManager\Service
 */
class AssetCacheManagerServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return AssetFilterManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = array();

        $globalConfig = $serviceLocator->get('config');

        if (!empty($globalConfig['asset_manager']['caching'])) {
            $config = $globalConfig['asset_manager']['caching'];
        }

        return new AssetCacheManager($serviceLocator, $config);
    }
}
