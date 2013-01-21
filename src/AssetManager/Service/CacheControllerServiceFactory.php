<?php

namespace AssetManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use AssetManager\Resolver\AggregateResolver;
use AssetManager\Exception;
use AssetManager\Service\CacheController;

/**
 * Factory class for AssetManagerService
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class CacheControllerServiceFactory implements FactoryInterface
{

    /**
     * {@inheritDoc}
     *
     * @return CacheController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config         = $serviceLocator->get('Config');
        $config         = isset($config['asset_manager']) ? $config['asset_manager'] : array();

        $cacheController = new CacheController($config);

        return $cacheController;
    }
}
