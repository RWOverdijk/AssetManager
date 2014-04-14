<?php

namespace AssetManager\Service;

use Assetic\Asset\AssetInterface;
use Assetic\Asset\AssetCache;
use Assetic\Cache\CacheInterface;

class AssetCacheManager
{
    /**
     * @var array Cache configuration.
     */
    protected $config;

    /**
     * @var array Cache Provider array
     */
    protected $cacheProviders;

    /**
     * Construct the AssetCacheManager
     *
     * @param   array $cacheProviders
     * @param   array $config
     * @return  AssetCacheManager
     */
    public function __construct(array $cacheProviders=array(), array $config=array())
    {
        $this->cacheProviders = $cacheProviders;
        $this->config = $config;
    }

    /**
     * Set the cache (if any) on the asset, and return the new AssetCache.
     *
     * @param   string$path
     * @param   AssetInterface $asset
     *
     * @return  AssetCache
     */
    public function setCache($path, AssetInterface $asset)
    {
        $caching = null;
        $config  = $this->config;

        if (!empty($config[$path])) {
            $caching = $config[$path];
        } elseif (!empty($config['default'])) {
            $caching = $config['default'];
        }

        if ($caching === null
            || empty($caching['cache'])
            || empty($this->cacheProviders[$path])
            || !$this->cacheProviders[$path] instanceof CacheInterface
        ) {
            return $asset;
        }

        $assetCache             = new AssetCache($asset, $this->cacheProviders[$path]);
        $assetCache->mimetype   = $asset->mimetype;

        return $assetCache;
    }


}
