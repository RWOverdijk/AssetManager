<?php

namespace AssetManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use AssetManager\Resolver\ConcatResolver;

class ConcatResolverServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return ConcatResolver
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config      = $serviceLocator->get('Config');
        $files = array();

        if (isset($config['asset_manager']['resolver_configs']['concat'])) {
            $files = $config['asset_manager']['resolver_configs']['concat'];
        }

        $concatResolver = new ConcatResolver($files);

        return $concatResolver;
    }
}
