<?php

namespace AssetManager\Service;

interface AssetCacheManagerAwareInterface
{
    /**
     * Set the AssetCacheManager.
     */
    public function setAssetCacheManager(AssetCacheManager $cacheManager);

    /**
     * Get the AssetCacheManager
     *
     * @return AssetCacheManager
     */
    public function getAssetCacheManager();
}
