<?php

namespace AssetManager\Service;

use Assetic\Asset\AssetInterface;
use Assetic\Asset\AssetCache;
use Assetic\Cache\CacheInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Asset Cache Manager.  Sets asset cache based on configuration.
 */
class AssetCacheManager
{
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var array Cache configuration.
     */
    protected $config = array();

    /**
     * Construct the AssetCacheManager
     *
     * @param   ServiceLocatorInterface $serviceLocator
     * @param   array                   $config
     *
     * @return  AssetCacheManager
     */
    public function __construct(
        ServiceLocatorInterface $serviceLocator,
        $config
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->config = $config;
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

        if ($this->serviceLocator->has($cacheProvider['cache'])) {
            return $this->serviceLocator->get($cacheProvider['cache']);
        }

        // Left here for BC.  Please consider defining a ZF2 service instead.
        if (is_callable($cacheProvider['cache'])) {
            return call_user_func($cacheProvider['cache'], $path);
        }

        $dir = '';
        $class = $cacheProvider['cache'];

        if (!empty($cacheProvider['options']['dir'])) {
            $dir = $cacheProvider['options']['dir'];
        }

        $class = $this->classMapper($class);
        return new $class($dir, $path);
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
