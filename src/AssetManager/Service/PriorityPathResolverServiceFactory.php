<?php

namespace AssetManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use AssetManager\Resolver\PriorityPathResolver;

class PriorityPathResolverServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return PriorityPathStackResolver
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config                     = $serviceLocator->get('config');
        $priorityPathResolver       = new PriorityPathResolver();

        if (empty($config['asset_manager']['prioritized_paths'])) {
            $config['asset_manager']['prioritized_paths'] = array();
        }

        $priorityPathResolver->addPaths($config['asset_manager']['prioritized_paths']);

        return $priorityPathResolver;
    }
}
