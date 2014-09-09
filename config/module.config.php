<?php
return array(
    'service_manager' => array (
        'factories' => array (
            'AssetManager\Service\AssetManager'                 => 'AssetManager\Service\AssetManagerServiceFactory',
            'AssetManager\Service\AssetFilterManager'           => 'AssetManager\Service\AssetFilterManagerServiceFactory',
            'AssetManager\Service\AssetCacheManager'            => 'AssetManager\Service\AssetCacheManagerServiceFactory',
            'AssetManager\Service\AggregateResolver'            => 'AssetManager\Service\AggregateResolverServiceFactory',
            'AssetManager\Resolver\MapResolver'                 => 'AssetManager\Service\MapResolverServiceFactory',
            'AssetManager\Resolver\PathStackResolver'           => 'AssetManager\Service\PathStackResolverServiceFactory',
            'AssetManager\Resolver\PrioritizedPathsResolver'    => 'AssetManager\Service\PrioritizedPathsResolverServiceFactory',
            'AssetManager\Resolver\CollectionResolver'          => 'AssetManager\Service\CollectionResolverServiceFactory',
            'AssetManager\Resolver\ConcatResolver'              => 'AssetManager\Service\ConcatResolverServiceFactory',
            'AssetManager\Resolver\AliasPathStackResolver'      => 'AssetManager\Service\AliasPathStackResolverServiceFactory',
        ),
        'invokables' => array(
            'AssetManager\Service\MimeResolver'                 => 'AssetManager\Service\MimeResolver',
        ),
        'aliases' => array(
            //Alias left here for BC
            'mime_resolver'                                     => 'AssetManager\Service\MimeResolver',
        ),
    ),
    'asset_manager' => array(
        'clear_output_buffer' => true,
        'resolvers' => array(
            'AssetManager\Resolver\MapResolver'                 => 3000,
            'AssetManager\Resolver\ConcatResolver'              => 2500,
            'AssetManager\Resolver\CollectionResolver'          => 2000,
            'AssetManager\Resolver\PrioritizedPathsResolver'    => 1500,
            'AssetManager\Resolver\AliasPathStackResolver'      => 1000,
            'AssetManager\Resolver\PathStackResolver'           => 500,
        ),
    ),
    'controllers'     => array(
        'factories' => array(
            'AssetManager\Controller\Console' => 'AssetManager\Controller\ConsoleControllerFactory',
        ),
    ),
    'console'         => array(
        'router' => array(
            'routes' => array(
                'AssetManager-warmup' => array(
                    'options' => array(
                        'route'    => 'assetmanager warmup [--purge] [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'AssetManager\Controller\Console',
                            'action'     => 'warmup',
                        ),
                    ),
                ),
            ),
        ),
    ),

);
