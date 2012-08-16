<?php

namespace AssetManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use AssetManager\Resolver\AggregateResolver;

/**
 * Factory class for AssetManagerService
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class AggregateResolverServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return AggregateResolver
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config   = $serviceLocator->get('Config');
        $config   = isset($config['asset_manager']) ? $config['asset_manager'] : array();
        $resolver = new AggregateResolver();

        if (empty($config['resolvers'])) {
            return $resolver;
        }

        foreach ($config['resolvers'] as $resolverService => $priority) {
            $resolverService = $serviceLocator->get($resolverService);
            $resolver->attach($resolverService, $priority);
        }

        return $resolver;
    }
}
