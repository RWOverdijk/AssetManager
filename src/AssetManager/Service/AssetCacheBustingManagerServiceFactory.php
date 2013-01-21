<?php

namespace AssetManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use AssetManager\Service\AssetCacheBustingManager;

class AssetCacheBustingManagerServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return AssetCacheBustingManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cacheBusting = array();
        $config  = $serviceLocator->get('Config');

        if (!empty($config['asset_manager']['cache_busting'])) {
            $cacheBusting = $config['asset_manager']['cache_busting'];
        }

        $assetCacheBustingManager = new AssetCacheBustingManager($cacheBusting);

        return $assetCacheBustingManager;
    }
}
