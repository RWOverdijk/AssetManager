<?php
namespace AssetManager\View\Helper;

use AssetManager\Asset\AggregateAsset;
use AssetManager\Exception\InvalidArgumentException;
use AssetManager\Resolver\ResolverInterface;
use AssetManager\Service\AssetManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\Cache\Storage\Adapter\AbstractAdapter as AbstractCacheAdapter;

class Asset extends AbstractHelper
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    /**
     * @var ResolverInterface
     */
    private $assetManagerResolver;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param ResolverInterface       $assetManagerResolver
     * @param array                   $config
     */
    public function __construct(ServiceLocatorInterface $serviceLocator, ResolverInterface $assetManagerResolver, $config)
    {
        $this->serviceLocator       = $serviceLocator;
        $this->assetManagerResolver = $assetManagerResolver;
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
        // check if the cache is configured
        if (!isset($this->config['view_helper']['cache']) || $this->config['view_helper']['cache'] == null) {
            return null;
        }

        // get the cache, if it's a string, search it among services
        $cache = $this->config['view_helper']['cache'];
        if (is_string($cache)) {
            $cache = $this->serviceLocator->get($cache);
        }

        // return if cache not found
        if ($cache == null) {
            return null;
        }

        // exception in case cache is not an Adapter that extend the AbstractAdapter of Zend\Cache\Storage
        if (!($cache instanceof AbstractCacheAdapter)) {
            throw new InvalidArgumentException(
                'Invalid cache provided, you must pass a Cache Adapter that extend Zend\Cache\Storage\Adapter\AbstractAdapter'
            );
        }

        // cache key based on the filename
        $cacheKey = md5($filename);
        $itemIsFoundInCache = false;
        $filePath = $cache->getItem($cacheKey, $itemIsFoundInCache);

        // if there is no element in the cache, elaborate and cache it
        if ($itemIsFoundInCache === false || $filePath === null) {
            $filePath = $this->elaborateFilePath($filename, $queryString);
            $cache->setItem($cacheKey, $filePath);
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
        } else if (isset($this->config['caching']['default'])) {
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
