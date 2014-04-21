<?php

namespace AssetManager\Service;

use Assetic\Cache\CacheInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory class for AssetManagerService
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class AssetCacheProviderServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return array
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config  = $serviceLocator->get('Config');
        $cachingDefinitions = array();

        $return = array();

        if (!empty($config['asset_manager']['caching'])) {
            $cachingDefinitions = $config['asset_manager']['caching'];
        }

        foreach ($cachingDefinitions as $path => $cacheProviderDefinition) {

            if (empty($cacheProviderDefinition['cache'])) {
                continue;

            } elseif (is_callable($cacheProviderDefinition['cache'])) {
                $provider = $cacheProviderDefinition['cache']($path);

            } elseif ($serviceLocator->has($cacheProviderDefinition['cache'])) {
                $provider = $serviceLocator->get($cacheProviderDefinition['cache']);

            } else {
                $dir = '';
                $class = $cacheProviderDefinition['cache'];

                if (!empty($options['dir'])) {
                    $dir = $options['dir'];
                }

                $class = $this->classMapper($class);
                $provider = new $class($dir, $path);
            }

            if (!$provider instanceof CacheInterface) {
                continue;
            }

            $return[$path] = $provider;
        }

        return $return;
    }

    /**
     * Class mapper to provide Backwards compatibility
     * @param $class
     *
     * @return string
     */
    private function classMapper($class)
    {
        $classToCheck = $class;
        $classToCheck .= (substr($class, -5) === 'Cache') ? '' : 'Cache';

        switch ($classToCheck) {
            case 'ApcCache':
                $class = 'Assetic\Cache\ApcCache';
                break;
            case 'FilesystemCache':
                $class = 'Assetic\Cache\FilesystemCache';
                break;
            case 'FilePathCache':
                $class = 'AssetManager\Cache\FilePathCache';
                break;
        }

        return $class;
    }
}
