<?php
return array(
    'service_manager' => array (
        'factories' => array (
            'AssetManager\Service\AssetManager'             => 'AssetManager\Service\AssetManagerServiceFactory',
            'AssetManager\Service\AggregateResolver'        => 'AssetManager\Service\AggregateResolverServiceFactory',
            'AssetManager\Resolver\MapResolver'             => 'AssetManager\Service\MapResolverServiceFactory',
            'AssetManager\Resolver\PathStackResolver'       => 'AssetManager\Service\PathStackResolverServiceFactory',
            'AssetManager\Resolver\PriorityPathResolver'    => 'AssetManager\Service\PriorityPathResolverServiceFactory',

            //'AssetManager\Resolver\AssetCollectionResolver'  => 'AssetManager\Service\AssetCollectionResolverServiceFactory',

        ),
    ),

    'asset_manager' => array(
        //'map' => array('my.js' => '/path/to/file.js'),
        //'paths' => array('/my/module/assets', '/my/other/directory/public'),

        'resolvers' => array(
            'AssetManager\Resolver\MapResolver'             => 2000,
            'AssetManager\Resolver\PriorityPathResolver'    => 1000,
            'AssetManager\Resolver\PathStackResolver'            => 1,
            //'AssetManager\Resolver\AssetCollectionResolver' => 500,
        ),
    ),
);
