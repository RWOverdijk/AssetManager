<?php
return array(
    'service_manager' => array (
        'factories' => array (
            'asset_manager'  => 'AssetManager\Service\AssetManagerServiceFactory',
            'asset_resolver' => 'AssetManager\Service\ResolverServiceFactory'
        )
    ),

    'asset_manager' => array(

    ),
);