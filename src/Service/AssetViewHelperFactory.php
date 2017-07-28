<?php

namespace AssetManager\Service;

use AssetManager\Core\Exception\InvalidArgumentException;
use AssetManager\Core\Resolver\ResolverInterface;
use AssetManager\Core\Service\AssetManager;
use AssetManager\View\Helper\Asset;
use Interop\Container\ContainerInterface;
use Zend\Cache\Storage\Adapter\AbstractAdapter as AbstractCacheAdapter;


class AssetViewHelperFactory
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config')['asset_manager'];

        /** @var ResolverInterface $assetManagerResolver */
        $assetManagerResolver = $container->get(AssetManager::class)->getResolver();

        /** @var AbstractCacheAdapter|null $cache */
        $cache = $this->loadCache($container, $config);

        return new Asset($assetManagerResolver, $cache, $config);
    }

    /**
     * @param ContainerInterface $container
     * @param array                   $config
     *
     * @return null
     */
    private function loadCache(ContainerInterface $container, $config)
    {
        // check if the cache is configured
        if (!isset($config['view_helper']['cache']) || $config['view_helper']['cache'] === null) {
            return null;
        }

        // get the cache, if it's a string, search it among services
        $cache = $config['view_helper']['cache'];
        if (is_string($cache)) {
            $cache = $container->get($cache);
        }

        // exception in case cache is not an Adapter that extend the AbstractAdapter of Zend\Cache\Storage
        if ($cache !== null && !($cache instanceof AbstractCacheAdapter)) {
            throw new InvalidArgumentException(
                'Invalid cache provided, you must pass a Cache Adapter that extend 
                Zend\Cache\Storage\Adapter\AbstractAdapter'
            );
        }

        return $cache;
    }
}
