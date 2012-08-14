<?php

namespace AssetManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use AssetManager\Resolver\PriorityPathStackResolver;

class PriorityPathStackResolverServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return PriorityPathStackResolver
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config                     = $serviceLocator->get('config');
        $priorityPathStackResolver  = new PriorityPathStackResolver();

        $priorityPathStackResolver->addPaths($config['asset_manager']['prioritized_paths']);

        return $priorityPathStackResolver;
    }
}
