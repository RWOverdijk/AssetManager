<?php

namespace AssetManager\Controller;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ConsoleControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();
        $console        = $serviceLocator->get('Console');
        $assetManager   = $serviceLocator->get('AssetManager\Service\AssetManager');
        $appConfig      = $serviceLocator->get('config');

        return new ConsoleController($console, $assetManager, $appConfig);
    }
}
