<?php
namespace AssetManager\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Asset extends AbstractHelper
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
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
