<?php

namespace AssetManager\Service;

use AssetManager\Exception\InvalidArgumentException;
use AssetManager\Resolver\ResolverInterface;
use AssetManager\Service\AssetManager;
use AssetManager\View\Helper\Asset;
use Interop\Container\ContainerInterface;
use Zend\Cache\Storage\Adapter\AbstractAdapter as AbstractCacheAdapter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
        $container = $serviceManager->getServiceLocator() ?: $serviceManager;
        return $this($container, Asset::class);
    }
}
