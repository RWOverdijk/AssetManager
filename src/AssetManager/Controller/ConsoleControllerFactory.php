<?php

namespace AssetManager\Controller;

use Interop\Container\ContainerInterface;
use AssetManager\Service\AssetManager;

class ConsoleControllerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $console        = $container->get('Console');
        $assetManager   = $container->get(AssetManager::class);
        $appConfig      = $container->get('config');

        return new ConsoleController($console, $assetManager, $appConfig);
    }
}
