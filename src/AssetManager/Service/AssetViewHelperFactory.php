<?php

namespace AssetManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use AssetManager\View\Helper\Asset;

class AssetViewHelperFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return Asset
     */
    public function createService(ServiceLocatorInterface $serviceManager)
    {
        /** @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $serviceManager->getServiceLocator();

        $config = $serviceLocator->get('config')['asset_manager'];

        /** @var AssetManager $assetManager */
        $assetManager = $serviceLocator->get('AssetManager\Service\AssetManager');

        return new Asset($serviceLocator, $assetManager, $config);
    }
}
