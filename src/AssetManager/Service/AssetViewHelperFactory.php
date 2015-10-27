<?php

namespace AssetManager\Service;

use AssetManager\Resolver\ResolverInterface;
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

        /** @var ResolverInterface $assetManagerResolver */
        $assetManagerResolver = $serviceLocator->get('AssetManager\Service\AssetManager')->getResolver();

        return new Asset($serviceLocator, $assetManagerResolver, $config);
    }
}
