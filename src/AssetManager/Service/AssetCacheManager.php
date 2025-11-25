<?php

namespace AssetManager\Service;

use Assetic\Asset\AssetCache;
use Assetic\Contracts\Asset\AssetInterface;
use Assetic\Contracts\Cache\CacheInterface;
use Psr\Container\ContainerInterface;

/**
 * Asset Cache Manager.  Sets asset cache based on configuration.
 */
class AssetCacheManager
{
    protected ContainerInterface $serviceLocator;

    /**
     * @var array Cache configuration.
     */
    protected $config = [];

    /**
     * Construct the AssetCacheManager
     *
     * @param ContainerInterface $serviceLocator
     * @param array              $config
     */
    public function __construct(
        ContainerInterface $serviceLocator,
        $config,
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->config         = $config;
    }

    /**
     * Set the cache (if any) on the asset, and return the new AssetCache.
     *
     * @param string         $path  Path to asset
     * @param AssetInterface $asset Assetic Asset Interface
     *
     * @return  AssetCache|AssetInterface
     */
    public function setCache($path, AssetInterface $asset)
    {
        $provider = $this->getProvider($path);

        if (!$provider instanceof CacheInterface) {
            return $asset;
        }

        $assetCache           = new AssetCache($asset, $provider);
        $assetCache->mimetype = $asset->mimetype;

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

        if (is_string($cacheProvider['cache']) &&
            $this->serviceLocator->has($cacheProvider['cache'])
        ) {
            return $this->serviceLocator->get($cacheProvider['cache']);
        }

        // Left here for BC.  Please consider defining a ZF2 service instead.
        if (is_callable($cacheProvider['cache'])) {
            return call_user_func($cacheProvider['cache'], $path);
        }

        $dir   = '';
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
