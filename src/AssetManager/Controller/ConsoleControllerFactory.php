<?php

namespace AssetManager\Controller;

use AssetManager\Service\AssetManager;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ConsoleControllerFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $console        = $container->get('console');
        $assetManager   = $container->get(AssetManager::class);
        $appConfig      = $container->get('config');

        return new ConsoleController($console, $assetManager, $appConfig);
    }

    /**
     * @inheritDoc
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if ($serviceLocator instanceof AbstractPluginManager) {
            $serviceLocator = $serviceLocator->getServiceLocator() ?: $serviceLocator;
        }
        return $this($serviceLocator, ConsoleController::class);
    }
}
