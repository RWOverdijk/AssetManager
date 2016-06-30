<?php

namespace AssetManager\Service;

use AssetManager\Resolver\AliasPathStackResolver;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use AssetManager\Resolver\PathStackResolver;

class AliasPathStackResolverServiceFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config  = $container->get('config');
        $aliases = array();

        if (isset($config['asset_manager']['resolver_configs']['aliases'])) {
            $aliases = $config['asset_manager']['resolver_configs']['aliases'];
        }

        return new AliasPathStackResolver($aliases);
    }

    /**
     * {@inheritDoc}
     *
     * @return PathStackResolver
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, AliasPathStackResolver::class);
    }
}
