<?php

namespace AssetManager\Service;

interface AssetCacheManagerAwareInterface
{
    /**
     * Set the AssetCacheManager.
     *
     * @param AssetCacheManager $cacheManager
     */
    public function setAssetCacheManager(AssetCacheManager $cacheManager);

    /**
     * Get the AssetCacheManager
     *
     * @return AssetCacheManager
     */
    public function getAssetCacheManager();
}
