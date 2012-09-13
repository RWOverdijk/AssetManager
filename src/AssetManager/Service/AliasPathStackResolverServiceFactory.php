<?php

namespace AssetManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use AssetManager\Resolver\AliasPathStackResolver;

class AliasPathStackResolverServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return PathStackResolver
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config                 = $serviceLocator->get('config');
        $aliasPathStackResolver = new AliasPathStackResolver();
        $aliases                = array();

        if (isset($config['asset_manager']['resolver_configs']['alias'])) {
            $aliases = $config['asset_manager']['resolver_configs']['alias'];
        }

        $aliasPathStackResolver->addAliases($aliases);

        return $aliasPathStackResolver;
    }
}
