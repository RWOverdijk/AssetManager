<?php

namespace AssetManager\Service;

use AssetManager\Service\CacheController;

interface CacheControllerAwareInterface
{
    public function setCacheController(CacheController $cacheController);
}
