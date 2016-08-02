<?php

namespace AssetManager\Service;

use Interop\Container\ContainerInterface;

/**
 * Factory class for AssetManagerService
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class AssetManagerServiceFactory
{

    /**
     * Build the Asset Manager Service
     * 
     * @param ContainerInterface $container Container Service
     *
     * @return AssetManager
     */
    public function __invoke(ContainerInterface $container)
    {
        $config             = $container->get('config');
        $assetManagerConfig = array();

        if (!empty($config['asset_manager'])) {
            $assetManagerConfig = $config['asset_manager'];
        }

        $assetManager = new AssetManager(
            $container->get('AssetManager\Service\AggregateResolver'),
            $assetManagerConfig
        );

        $assetManager->setAssetFilterManager(
            $container->get('AssetManager\Service\AssetFilterManager')
        );

        $assetManager->setAssetCacheManager(
            $container->get('AssetManager\Service\AssetCacheManager')
        );

        return $assetManager;
    }
}
