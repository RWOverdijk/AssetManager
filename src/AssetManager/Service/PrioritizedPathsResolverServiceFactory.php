<?php

namespace AssetManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use AssetManager\Resolver\PrioritizedPathsResolver;

class PrioritizedPathsResolverServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return PriorityPathStackResolver
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config                     = $serviceLocator->get('config');
        $prioritizedPathsResolver   = new PrioritizedPathsResolver();

        if (empty($config['asset_manager']['prioritized_paths'])) {
            $config['asset_manager']['prioritized_paths'] = array();
        }

        $prioritizedPathsResolver->addPaths($config['asset_manager']['prioritized_paths']);

        return $prioritizedPathsResolver;
    }
}
