<?php

namespace AssetManager\Service;

use Interop\Container\ContainerInterface;

/**
 * Factory for the Asset Cache Manager Service
 *
 * @package AssetManager\Service
 */
class AssetCacheManagerServiceFactory
{
    /**
     * Build the Asset Cache Manager
     * 
     * @param ContainerInterface $container Container Service
     *
     * @return AssetFilterManager
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = array();

        $globalConfig = $container->get('config');

        if (!empty($globalConfig['asset_manager']['caching'])) {
            $config = $globalConfig['asset_manager']['caching'];
        }

        return new AssetCacheManager($container, $config);
    }
}
