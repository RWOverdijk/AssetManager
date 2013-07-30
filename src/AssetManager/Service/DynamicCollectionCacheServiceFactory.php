<?php
namespace AssetManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DynamicCollectionCacheServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return DynamicCollectionCache
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config  = $serviceLocator->get('Config');
        $options = null;

        if (isset($config['asset_manager']['dynamic_collection_cache'])) {
            $options = $config['asset_manager']['dynamic_collection_cache'];
        }
        try {
        return new DynamicCollectionCache(
            new DynamicCollectionCacheOptions($options)
        );
        } catch (\Exception $e) {
            echo $e->getMessage();die();
        }
    }
}
