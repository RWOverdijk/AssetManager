<?php

namespace AssetManager\Controller;

use AssetManager\Core\Service\AssetManager;
use Interop\Container\ContainerInterface;

class ConsoleControllerFactory
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
}
