<?php

namespace AssetManager;

class ConfigProvider
{
    public function __invoke(): array
    {
        /**
         * @noinspection PhpFullyQualifiedNameUsageInspection
         */
        return [
            'dependencies'  => [
                'factories'  => [
                    \AssetManager\Service\AssetManager::class              => \AssetManager\Service\AssetManagerServiceFactory::class,
                    \AssetManager\Service\AssetFilterManager::class        => \AssetManager\Service\AssetFilterManagerServiceFactory::class,
                    \AssetManager\Service\AssetCacheManager::class         => \AssetManager\Service\AssetCacheManagerServiceFactory::class,
                    \AssetManager\Resolver\AggregateResolver::class        => \AssetManager\Service\AggregateResolverServiceFactory::class,
                    \AssetManager\Resolver\MapResolver::class              => \AssetManager\Service\MapResolverServiceFactory::class,
                    \AssetManager\Resolver\PathStackResolver::class        => \AssetManager\Service\PathStackResolverServiceFactory::class,
                    \AssetManager\Resolver\PrioritizedPathsResolver::class => \AssetManager\Service\PrioritizedPathsResolverServiceFactory::class,
                    \AssetManager\Resolver\CollectionResolver::class       => \AssetManager\Service\CollectionResolverServiceFactory::class,
                    \AssetManager\Resolver\ConcatResolver::class           => \AssetManager\Service\ConcatResolverServiceFactory::class,
                    \AssetManager\Resolver\AliasPathStackResolver::class   => \AssetManager\Service\AliasPathStackResolverServiceFactory::class,
                    \AssetManager\Service\MimeResolver::class              => \Laminas\ServiceManager\Factory\InvokableFactory::class,
                ],
            ],
            'asset_manager' => [
                'clear_output_buffer' => true,
                'resolvers'           => [
                    \AssetManager\Resolver\MapResolver::class              => 3000,
                    \AssetManager\Resolver\ConcatResolver::class           => 2500,
                    \AssetManager\Resolver\CollectionResolver::class       => 2000,
                    \AssetManager\Resolver\PrioritizedPathsResolver::class => 1500,
                    \AssetManager\Resolver\AliasPathStackResolver::class   => 1000,
                    \AssetManager\Resolver\PathStackResolver::class        => 500,
                ],
                'view_helper'         => [
                    'append_timestamp' => true,
                    'query_string'     => '_',
                    'cache'            => null,
                ],
            ],
        ];
    }

}