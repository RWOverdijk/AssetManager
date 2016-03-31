<?php

namespace AssetManager\Service;

use AssetManager\Exception\InvalidArgumentException;
use AssetManager\Resolver\ResolverInterface;
use AssetManager\View\Helper\Asset;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Cache\Storage\Adapter\AbstractAdapter as AbstractCacheAdapter;

class AssetViewHelperFactory implements FactoryInterface
{

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

        // exception in case cache is not an Adapter that extend the AbstractAdapter of Zend\Cache\Storage
        if ($cache !== null && !($cache instanceof AbstractCacheAdapter)) {
            throw new InvalidArgumentException(
                'Invalid cache provided, you must pass a Cache Adapter that extend 
                Zend\Cache\Storage\Adapter\AbstractAdapter'
            );
        }

        return $cache;
    }

    /**
     * {@inheritDoc}
     *
     * @return Asset
     */
    public function createService(ServiceLocatorInterface $serviceManager)
    {
        /** @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $serviceManager->getServiceLocator();

        $config = $serviceLocator->get('config')['asset_manager'];

        /** @var ResolverInterface $assetManagerResolver */
        $assetManagerResolver = $serviceLocator->get('AssetManager\Service\AssetManager')->getResolver();

        /** @var AbstractCacheAdapter|null $cache */
        $cache = $this->loadCache($serviceLocator, $config);

        return new Asset($assetManagerResolver, $cache, $config);
    }
}
