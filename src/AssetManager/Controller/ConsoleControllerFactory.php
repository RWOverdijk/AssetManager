<?php

namespace AssetManager\Controller;

use Interop\Container\ContainerInterface;

class ConsoleControllerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $console        = $container->get('Console');
        $assetManager   = $container->get('AssetManager\Service\AssetManager');
        $appConfig      = $container->get('config');

        return new ConsoleController($console, $assetManager, $appConfig);
    }
}
