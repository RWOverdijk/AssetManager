<?php

namespace AssetManager\Service;

use Psr\Container\ContainerInterface;

/**
 * Factory for the Asset Cache Manager Service
 *
 * @package AssetManager\Service
 */
class AssetCacheManagerServiceFactory
{
    /**
     * @inheritDoc
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
