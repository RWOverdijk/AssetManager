<?php

namespace AssetManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use AssetManager\Resolver\AggregateResolver;
use AssetManager\Resolver\PathStack;
use AssetManager\Resolver\MapResolver;

/**
 * Factory class for AssetManagerService
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class ResolverServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return AggregateResolver
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config   = $serviceLocator->get('Config');
        $resolver = new AggregateResolver();

        if (isset($config['asset_manager']['map'])) {
            $mapResolver = new MapResolver($config['asset_manager']['map']);
            $resolver->attach($mapResolver, 1000);
        }

        if (isset($config['asset_manager']['paths'])) {
            $pathStackResolver = new PathStack();
            $pathStackResolver->addPaths($config['asset_manager']['paths']);
            $resolver->attach($pathStackResolver);
        }

        return $resolver;
    }
}
