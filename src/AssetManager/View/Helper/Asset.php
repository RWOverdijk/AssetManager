<?php
namespace AssetManager\View\Helper;

use AssetManager\Exception\InvalidArgumentException;
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
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        $this->config         = $serviceLocator->get('config')['asset_manager'];
    }

    /**
     * find the file and if it exists, append its unix modification time to the filename
     *
     * @param string $assetsPath
     * @param string $filename
     * @param string $queryString
     * @return string
     */
    private function elaborateFilePath($assetsPath, $filename, $queryString)
    {
        // resolve asset
        $asset = $this->serviceLocator->get('AssetManager\Service\AssetManager')->getResolver()->resolve($filename);
        if ($asset !== null) {

            // append last modified date to the filepath and use a custom query string
            return $filename . '?' . $queryString . '=' . $asset->getLastModified();
        }

        return $filename;
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

        // find assets path specified from cache
        $assetsPath = $cacheConfig['options']['dir'];

        // query string params
        $queryString = isset($this->config['view_helper']['query_string'])
            ? $this->config['view_helper']['query_string']
            : '_';

        // cache
        if (isset($this->config['view_helper']['cache']) && $this->config['view_helper']['cache'] != null) {

            // get the cache, if it's a string, search it among services
            $cache = $this->config['view_helper']['cache'];
            if (is_string($cache)) {
                $cache = $this->serviceLocator->get($cache);
            }

            if ($cache != null) {

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
                    $filePath = $this->elaborateFilePath($assetsPath, $filename, $queryString);
                    $cache->setItem($cacheKey, $filePath);
                }

                return $filePath;
            }
        }

        return $this->elaborateFilePath($assetsPath, $filename, $queryString);
    }
}
