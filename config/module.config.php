<?php
$coreConfig = AssetManager\Core\Config\Config::getConfig();

$services = [];

if (!empty($coreConfig['dependencies'])) {
    $services = $coreConfig['dependencies'];
}

$assetManagerConfig = [];

if (!empty($coreConfig['asset_manager'])) {
    $assetManagerConfig = $coreConfig['asset_manager'];
}

return [
    'service_manager' => $services,
    'asset_manager'   => $assetManagerConfig,
    'controllers'     => [
        'factories' => [
            \AssetManager\Controller\ConsoleController::class
            => AssetManager\Controller\ConsoleControllerFactory::class,
        ],
    ],
    'view_helpers'    => [
        'factories' => [
            \AssetManager\View\Helper\Asset::class => AssetManager\Service\AssetViewHelperFactory::class,
        ],
    ],
    'console'         => [
        'router' => [
            'routes' => [
                'AssetManager-warmup' => [
                    'options' => [
                        'route'    => 'assetmanager warmup [--purge] [--verbose|-v]',
                        'defaults' => [
                            'controller' => \AssetManager\Controller\ConsoleController::class,
                            'action'     => 'warmup',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
