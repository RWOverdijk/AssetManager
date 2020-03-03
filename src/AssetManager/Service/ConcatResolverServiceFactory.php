<?php

namespace AssetManager\Service;

use AssetManager\Resolver\ConcatResolver;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ConcatResolverServiceFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config      = $container->get('config');
        $files = array();

        if (isset($config['asset_manager']['resolver_configs']['concat'])) {
            $files = $config['asset_manager']['resolver_configs']['concat'];
        }

        $concatResolver = new ConcatResolver($files);

        return $concatResolver;
    }

    /**
     * {@inheritDoc}
     *
     * @return ConcatResolver
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, ConcatResolver::class);
    }
}
