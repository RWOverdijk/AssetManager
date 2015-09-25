<?php
namespace AssetManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use AssetManager\View\Helper\Asset;

class AssetViewHelperFactory implements FactoryInterface {

    /**
     * {@inheritDoc}
     *
     * @return Asset
     */
    public function createService(ServiceLocatorInterface $serviceManager)
    {
        return new Asset($serviceManager->getServiceLocator());
    }

}
