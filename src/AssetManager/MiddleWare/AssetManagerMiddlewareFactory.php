<?php

namespace AssetManager\MiddleWare;

use Interop\Container\ContainerInterface;

class AssetManagerMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new AssetManagerMiddleware(
            $container->get('AssetManager\Service\AssetManager')
        );
    }
}
