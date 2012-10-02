<?php

namespace AssetManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory class for AssetManagerService
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class AssetManagerServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return AssetManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $config = isset($config['asset_manager']) ? $config['asset_manager'] : array();
        
        $assetManager = new AssetManager(
            $serviceLocator->get('AssetManager\Service\AggregateResolver'),
            $config
        );

        $assetManager->setMimeResolver($serviceLocator->get('mime_resolver'));
        
        return $assetManager;
    }
}
