<?php

namespace AssetManager\Controller;

use AssetManager\Service\AssetManager;
use Psr\Container\ContainerInterface;

class ConsoleControllerFactory
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container)
    {
        $console        = $container->get('console');
        $assetManager   = $container->get(AssetManager::class);
        $appConfig      = $container->get('config');

        return new ConsoleController($console, $assetManager, $appConfig);
    }
}
