<?php

namespace AssetManager\Service;

use AssetManager\Resolver\AggregateResolver;
use Psr\Container\ContainerInterface;

/**
 * Factory class for AssetManagerService
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class AssetManagerServiceFactory
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container)
    {
        $config             = $container->get('config');
        $assetManagerConfig = array();

        if (!empty($config['asset_manager'])) {
            $assetManagerConfig = $config['asset_manager'];
        }

        $assetManager = new AssetManager(
            $container->get(AggregateResolver::class),
            $assetManagerConfig
        );

        $assetManager->setAssetFilterManager(
            $container->get(AssetFilterManager::class)
        );

        $assetManager->setAssetCacheManager(
            $container->get(AssetCacheManager::class)
        );

        return $assetManager;
    }
}
