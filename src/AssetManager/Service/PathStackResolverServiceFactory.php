<?php

namespace AssetManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use AssetManager\Resolver\PathStackResolver;

class PathStackResolverServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return PathStackResolver
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config             = $serviceLocator->get('config');
        $patchStackResolver = new PathStackResolver();

        $patchStackResolver->addPaths($config['asset_manager']['paths']);

        return $patchStackResolver;
    }
}
