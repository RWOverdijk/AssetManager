<?php

namespace AssetManager\Service;

use AssetManager\Resolver\GlobPathStackResolver;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class GlobPathStackResolverServiceFactory implements FactoryInterface {
    /**
     * {@inheritDoc}
     *
     * @return GlobPathStackResolver
     */
    public function createService( ServiceLocatorInterface $serviceLocator ) {
        $config            = $serviceLocator->get( 'config' );
        $pathStackResolver = new GlobPathStackResolver();
        $paths             = array();

        if ( isset( $config['asset_manager']['resolver_configs']['paths'] ) ) {
            $paths = $config['asset_manager']['resolver_configs']['paths'];
        }

        $pathStackResolver->addPaths( $paths );

        return $pathStackResolver;
    }
}
