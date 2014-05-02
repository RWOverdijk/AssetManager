<?php

namespace AssetManager\Service;

use Assetic\Asset\AssetInterface;
use Assetic\Asset\AssetCache;
use Assetic\Cache\CacheInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Asset Cache Manager.  Sets asset cache based on configuration.
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class AssetCacheManager
{
    protected $serviceLocator;

    /**
     * @var array Cache configuration.
     */
    protected $config = array();

    /**
     * Construct the AssetCacheManager
     *
     * @param   ServiceLocatorInterface $serviceLocator
     *
     * @return  AssetCacheManager
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        $globalConfig = $this->serviceLocator->get('config');

        if (!empty($globalConfig['asset_manager']['caching'])) {
            $this->config = $globalConfig['asset_manager']['caching'];
        }
    }

    /**
     * Set the cache (if any) on the asset, and return the new AssetCache.
     *
     * @param string         $path  Path to asset
     * @param AssetInterface $asset Assetic Asset Interface
     *
     * @return  AssetCache
     */
    public function setCache($path, AssetInterface $asset)
    {
        $provider = $this->getProvider($path);

        if (!$provider instanceof CacheInterface) {
            return $asset;
        }

        $assetCache             = new AssetCache($asset, $provider);
        $assetCache->mimetype   = $asset->mimetype;

        return $assetCache;
    }

    /**
     * Get the cache provider.  First checks to see if the provider is callable,
     * then will attempt to get it from the service locator, finally will fallback
     * to a class mapper.
     *
     * @param $path
     *
     * @return array
     */
    private function getProvider($path)
    {
        $cacheProvider = $this->getCacheProviderConfig($path);

        if (!$cacheProvider) {
            return null;
        }

        if (is_callable($cacheProvider['cache'])) {
            $provider = call_user_func($cacheProvider['cache'], $path);
        } elseif ($this->serviceLocator->has($cacheProvider['cache'])) {
            $provider = $this->serviceLocator->get($cacheProvider['cache']);
        } else {
            $dir = '';
            $class = $cacheProvider['cache'];

            if (!empty($cacheProvider['options']['dir'])) {
                $dir = $cacheProvider['options']['dir'];
            }

            $class = $this->classMapper($class);
            $provider = new $class($dir, $path);
        }

        return $provider;
    }

    /**
     * Get the cache provider config.  Use default values if defined.
     *
     * @param $path
     *
     * @return null|array Cache config definition.  Returns null if not found in
     *                    config.
     */
    private function getCacheProviderConfig($path)
    {
        $cacheProvider = null;

        if (!empty($this->config[$path]) && !empty($this->config[$path]['cache'])) {
            $cacheProvider = $this->config[$path];
        }

        if (!$cacheProvider
            && !empty($this->config['default'])
            && !empty($this->config['default']['cache'])
        ) {
            $cacheProvider = $this->config['default'];
        }

        return $cacheProvider;
    }

    /**
     * Class mapper to provide backwards compatibility
     *
     * @param $class
     *
     * @return string
     */
    private function classMapper($class)
    {
        $classToCheck = $class;
        $classToCheck .= (substr($class, -5) === 'Cache') ? '' : 'Cache';

        switch ($classToCheck) {
            case 'ApcCache':
                $class = 'Assetic\Cache\ApcCache';
                break;
            case 'FilesystemCache':
                $class = 'Assetic\Cache\FilesystemCache';
                break;
            case 'FilePathCache':
                $class = 'AssetManager\Cache\FilePathCache';
                break;
        }

        return $class;
    }
}
