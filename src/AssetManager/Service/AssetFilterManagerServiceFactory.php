<?php

namespace AssetManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AssetFilterManagerServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return AssetFilterManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $filters = array();
        $config  = $serviceLocator->get('Config');

        if (!empty($config['asset_manager']['filters'])) {
            $filters = $config['asset_manager']['filters'];
        }

        $assetFilterManager = new AssetFilterManager($filters);

        $assetFilterManager->setServiceLocator($serviceLocator);
        $assetFilterManager->setMimeResolver($serviceLocator->get('AssetManager\Service\MimeResolver'));

        return $assetFilterManager;
    }
}
