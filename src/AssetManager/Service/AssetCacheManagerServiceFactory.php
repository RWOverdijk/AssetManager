<?php

namespace AssetManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AssetCacheManagerServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return AssetFilterManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $filters = array();
        $config  = $serviceLocator->get('Config');

        if (!empty($config['asset_manager']['caching'])) {
            $filters = $config['asset_manager']['caching'];
        }

        $assetCacheManager = new AssetCacheManager($filters);

        return $assetCacheManager;
    }
}
