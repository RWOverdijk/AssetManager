<?php
return [
    'service_manager' => [
        'factories'  => [
            AssetManager\Command\Warmup::class                    => AssetManager\Command\WarmupFactory::class,
            AssetManager\Service\AssetManager::class              => AssetManager\Service\AssetManagerServiceFactory::class,
            AssetManager\Service\AssetFilterManager::class        => AssetManager\Service\AssetFilterManagerServiceFactory::class,
            AssetManager\Service\AssetCacheManager::class         => AssetManager\Service\AssetCacheManagerServiceFactory::class,
            AssetManager\Resolver\AggregateResolver::class        => AssetManager\Service\AggregateResolverServiceFactory::class,
            AssetManager\Resolver\MapResolver::class              => AssetManager\Service\MapResolverServiceFactory::class,
            AssetManager\Resolver\PathStackResolver::class        => AssetManager\Service\PathStackResolverServiceFactory::class,
            AssetManager\Resolver\PrioritizedPathsResolver::class => AssetManager\Service\PrioritizedPathsResolverServiceFactory::class,
            AssetManager\Resolver\CollectionResolver::class       => AssetManager\Service\CollectionResolverServiceFactory::class,
            AssetManager\Resolver\ConcatResolver::class           => AssetManager\Service\ConcatResolverServiceFactory::class,
            AssetManager\Resolver\AliasPathStackResolver::class   => AssetManager\Service\AliasPathStackResolverServiceFactory::class,
        ],
        'invokables' => [
            AssetManager\Service\MimeResolver::class => AssetManager\Service\MimeResolver::class,
        ],
        'aliases'    => [
            //Alias left here for BC
            'mime_resolver'                             => AssetManager\Service\MimeResolver::class,
            'AssetManager\Service\AggregateResolver'    => AssetManager\Resolver\AggregateResolver::class
        ],
    ],
    'asset_manager'   => [
        'clear_output_buffer' => true,
        'resolvers'           => [
            AssetManager\Resolver\MapResolver::class              => 3000,
            AssetManager\Resolver\ConcatResolver::class           => 2500,
            AssetManager\Resolver\CollectionResolver::class       => 2000,
            AssetManager\Resolver\PrioritizedPathsResolver::class => 1500,
            AssetManager\Resolver\AliasPathStackResolver::class   => 1000,
            AssetManager\Resolver\PathStackResolver::class        => 500,
        ],
        'view_helper'         => [
            'append_timestamp' => true,
            'query_string'     => '_',
            'cache'            => null,
        ],
    ],
    'view_helpers'    => [
        'factories' => [
            AssetManager\View\Helper\Asset::class => AssetManager\Service\AssetViewHelperFactory::class
        ],
        'aliases' => [
            'asset' => AssetManager\View\Helper\Asset::class
        ]
    ],
    'laminas-cli' => [
        'commands' => [
            'AssetManager:warmup' => AssetManager\Command\Warmup::class
        ]
    ]
];
