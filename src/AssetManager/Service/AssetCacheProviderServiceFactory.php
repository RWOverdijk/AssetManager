<?php

namespace AssetManager\Service;

use Assetic\Cache\ApcCache;
use Assetic\Cache\FilesystemCache;
use AssetManager\Cache\FilePathCache;
use AssetManager\Exception\RuntimeException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory class for AssetManagerService
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class AssetCacheProviderServiceFactory implements FactoryInterface
{

    /**
     * {@inheritDoc}
     *
     * @return array
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config  = $serviceLocator->get('Config');
        $cachingDefinitions = array();

        $providers = array();

        if (!empty($config['asset_manager']['caching'])) {
            $cachingDefinitions = $config['asset_manager']['caching'];
        }

        foreach ($cachingDefinitions as $path => $cacheProviderDefinition) {
            if (empty($cacheProviderDefinition['cache'])) {
                continue;
            } elseif (is_callable($cacheProviderDefinition['cache'])) {
                $providers[$path] = $cacheProviderDefinition['cache']($path);
            } elseif ($serviceLocator->has($cacheProviderDefinition['cache'])) {
                $providers[$path] = $serviceLocator->get($cacheProviderDefinition['cache']);
            } else {
                $dir = '';

                if (!empty($options['dir'])) {
                    $dir = $options['dir'];
                }

                $providers[$path] = new $cacheProviderDefinition['cache']($dir, $path);
            }
        }

        return $providers;
    }

}
