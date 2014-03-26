<?php

namespace AssetManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory class for AssetManagerService
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class AssetManagerServiceFactory implements FactoryInterface
{

    /**
     * {@inheritDoc}
     *
     * @return AssetManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config             = $serviceLocator->get('Config');
        $assetManagerConfig = array();

        if (!empty($config['asset_manager'])) {
            $assetManagerConfig = $config['asset_manager'];
        }

        $assetManager = new AssetManager(
            $serviceLocator->get('AssetManager\Service\AggregateResolver'),
            $assetManagerConfig
        );

        $assetManager->setAssetFilterManager(
            $serviceLocator->get('AssetManager\Service\AssetFilterManager')
        );

        $assetManager->setAssetCacheManager(
            $serviceLocator->get('AssetManager\Service\AssetCacheManager')
        );

        return $assetManager;
    }
}
