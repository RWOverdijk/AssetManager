<?php
namespace AssetManager\View\Helper;

use AssetManager\Service\AssetCacheManager;
use Zend\View\Helper\AbstractHelper;

class Asset extends AbstractHelper
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var AssetCacheManager The AssetCacheManager service.
     */
    protected $cacheManager;

    public function __construct($config, $assetCacheManager)
    {
        $this->config = $config;
        $this->cacheManager = $assetCacheManager;
    }

    public function __invoke($filename)
    {
        // find assets path specified from cache
        if (isset($this->config['asset_manager']['caching']['default']['options']['dir'])) {

            $assetsPath = $this->config['asset_manager']['caching']['default']['options']['dir'];

            // query string params
            $queryString = isset($this->config['asset_manager']['query_string'])
                ? $this->config['asset_manager']['query_string']
                : '_';

            // find the file and if it exists, append its unix modification time to the filename
            $originalPath = $assetsPath . $filename;
            if (file_exists($originalPath)) {
                return $filename . '?' . $queryString . '=' . filemtime($originalPath);
            }
        }

        return $filename;
    }
}
