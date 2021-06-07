<?php

namespace AssetManager\Command;

use AssetManager\Service\AssetManager;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class WarmupFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Warmup
    {
        $assetManager = $container->get(AssetManager::class);
        $appConfig = $container->get('config');

        return new Warmup($assetManager, $appConfig);
    }
}
