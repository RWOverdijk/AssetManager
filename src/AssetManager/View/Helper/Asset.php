<?php
namespace AssetManager\View\Helper;

use AssetManager\Resolver\ResolverInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\Cache\Storage\Adapter\AbstractAdapter as AbstractCacheAdapter;

class Asset extends AbstractHelper
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var ResolverInterface
     */
    private $assetManagerResolver;

    /**
     * @param ResolverInterface         $assetManagerResolver
     * @param AbstractCacheAdapter|null $cache
     * @param array                     $config
     */
    public function __construct(ResolverInterface $assetManagerResolver, $cache, $config)
    {
        $this->assetManagerResolver = $assetManagerResolver;
        $this->cache                = $cache;
        $this->config               = $config;
    }

    /**
     * find the file and if it exists, append its unix modification time to the filename
     *
     * @param string $filename
     * @param string $queryString
     * @return string
     */
    private function elaborateFilePath($filename, $queryString)
    {
        $asset = $this->assetManagerResolver->resolve($filename);
        if ($asset !== null) {

            // append last modified date to the filepath and use a custom query string
            return $filename . '?' . urlencode($queryString) . '=' . $asset->getLastModified();
        }

        return $filename;
    }

    /**
     * Use the cache to get the filePath
     *
     * @param string $filename
     * @param string $queryString
     *
     * @return mixed|string
     */
    private function getFilePathFromCache($filename, $queryString)
    {
        // return if cache not found
        if ($this->cache == null) {
            return null;
        }

        // cache key based on the filename
        $cacheKey = md5($filename);
        $itemIsFoundInCache = false;
        $filePath = $this->cache->getItem($cacheKey, $itemIsFoundInCache);

        // if there is no element in the cache, elaborate and cache it
        if ($itemIsFoundInCache === false || $filePath === null) {
            $filePath = $this->elaborateFilePath($filename, $queryString);
            $this->cache->setItem($cacheKey, $filePath);
        }

        return $filePath;
    }

    /**
     * Output the filepath with its unix modification time as query param
     *
     * @param string $filename
     * @return string
     */
    public function __invoke($filename)
    {
        // search the cache config for the specific file requested (if none, use the default one)
        if (isset($this->config['caching'][$filename])) {
            $cacheConfig = $this->config['caching'][$filename];
        } elseif (isset($this->config['caching']['default'])) {
            $cacheConfig = $this->config['caching']['default'];
        }

        // if nothing done, return the original filename
        if (!isset($cacheConfig['options']['dir'])) {
            return $filename;
        }

        // query string params
        $queryString = isset($this->config['view_helper']['query_string'])
            ? $this->config['view_helper']['query_string']
            : '_';

        // get the filePath from the cache (if available)
        $filePath = $this->getFilePathFromCache($filename, $queryString);
        if ($filePath !== null) {
            return $filePath;
        }

        return $this->elaborateFilePath($filename, $queryString);
    }
}
