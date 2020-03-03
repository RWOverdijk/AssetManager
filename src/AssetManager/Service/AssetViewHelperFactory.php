<?php

namespace AssetManager\Service;

use AssetManager\Exception\InvalidArgumentException;
use AssetManager\Resolver\ResolverInterface;
use AssetManager\View\Helper\Asset;
use Interop\Container\ContainerInterface;
use Laminas\Cache\Storage\Adapter\AbstractAdapter as AbstractCacheAdapter;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\AbstractPluginManager;

class AssetViewHelperFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')['asset_manager'];

        /** @var ResolverInterface $assetManagerResolver */
        $assetManagerResolver = $container->get(AssetManager::class)->getResolver();

        /** @var AbstractCacheAdapter|null $cache */
        $cache = $this->loadCache($container, $config);

        return new Asset($assetManagerResolver, $cache, $config);
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param array                   $config
     *
     * @return null
     */
    private function loadCache($serviceLocator, $config)
    {
        // check if the cache is configured
        if (!isset($config['view_helper']['cache']) || $config['view_helper']['cache'] === null) {
            return null;
        }

        // get the cache, if it's a string, search it among services
        $cache = $config['view_helper']['cache'];
        if (is_string($cache)) {
            $cache = $serviceLocator->get($cache);
        }

        // exception in case cache is not an Adapter that extend the AbstractAdapter of Laminas\Cache\Storage
        if ($cache !== null && !($cache instanceof AbstractCacheAdapter)) {
            throw new InvalidArgumentException(
                'Invalid cache provided, you must pass a Cache Adapter that extend 
                Laminas\Cache\Storage\Adapter\AbstractAdapter'
            );
        }

        return $cache;
    }

    /**
     * {@inheritDoc}
     *
     * @return Asset
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if ($serviceLocator instanceof AbstractPluginManager) {
            $serviceLocator = $serviceLocator->getServiceLocator() ?: $serviceLocator;
        }
        return $this($serviceLocator, Asset::class);
    }
}
