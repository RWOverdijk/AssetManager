<?php
return array(
    'dependencies' => array (
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
            \AssetManager\MiddleWare\AssetManagerMiddleware::class => \AssetManager\MiddleWare\AssetManagerMiddlewareFactory::class,
        ),
        'invokables' => array(
            'AssetManager\Service\MimeResolver'                 => 'AssetManager\Service\MimeResolver',
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
        'view_helper' => array(
            'append_timestamp' => true,
            'query_string'     => '_',
            'cache'            => null,
        ),
        'resolver_configs' => [
            'aliases' => [
                'test/' => __DIR__ . '/../public/',
            ],
        ],
    ),
    
    'view_helpers' => array(
        'factories' => array(
            'asset' => 'AssetManager\Service\AssetViewHelperFactory',
        ),
    ),

    'middleware_pipeline' => [
        'assetManager' => [
            'middleware' => [
                \AssetManager\MiddleWare\AssetManagerMiddleware::class => \AssetManager\MiddleWare\AssetManagerMiddleware::class
            ],
            'priority' => -1000000,
        ],
    ],
);
