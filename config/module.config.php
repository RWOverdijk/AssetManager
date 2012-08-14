<?php
return array(
    'service_manager' => array (
        'factories' => array (
            'AssetManager\Service\AssetManager'      => 'AssetManager\Service\AssetManagerServiceFactory',
            'AssetManager\Service\AggregateResolver' => 'AssetManager\Service\AggregateResolverServiceFactory'
        )
    ),

    'asset_manager' => array(
        //'map' => array('my.js' => '/path/to/file.js'),
        //'paths' => array('/my/module/assets', '/my/other/directory/public'),
    ),
);
