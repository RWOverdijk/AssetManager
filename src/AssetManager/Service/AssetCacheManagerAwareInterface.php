<?php

namespace AssetManager\Service;

interface AssetCacheManagerAwareInterface
{
    /**
     * Set the AssetCacheManager.
     *
     * @param AssetCacheManager $filterManager
     */
    public function setAssetCacheManager(AssetCacheManager $cacheManager);

    /**
     * Get the AssetCacheManager
     *
     * @return AssetCacheManager
     */
    public function getAssetCacheManager();
}
