<?php
return array(
    'service_manager' => array (
        'factories' => array (
            'AssetManager\Service\AssetManager'                 => 'AssetManager\Service\AssetManagerServiceFactory',
            'AssetManager\Service\AssetFilterManager'           => 'AssetManager\Service\AssetFilterManagerServiceFactory',
            'AssetManager\Service\AssetCacheManager'            => 'AssetManager\Service\AssetCacheManagerServiceFactory',
            'AssetManager\Service\AggregateResolver'            => 'AssetManager\Service\AggregateResolverServiceFactory',
            'AssetManager\Service\CacheController'              => 'AssetManager\Service\CacheControllerServiceFactory',
            'AssetManager\Service\AssetCacheBustingManager'     => 'AssetManager\Service\AssetCacheBustingManagerServiceFactory',
            'AssetManager\Resolver\MapResolver'                 => 'AssetManager\Service\MapResolverServiceFactory',
            'AssetManager\Resolver\PathStackResolver'           => 'AssetManager\Service\PathStackResolverServiceFactory',
            'AssetManager\Resolver\PrioritizedPathsResolver'    => 'AssetManager\Service\PrioritizedPathsResolverServiceFactory',
            'AssetManager\Resolver\CollectionResolver'          => 'AssetManager\Service\CollectionResolverServiceFactory',
        ),
        'invokables' => array(
            'mime_resolver' => 'AssetManager\Service\MimeResolver',
        ),
    ),
    'view_helpers' => array(
        'factories' => array(
            'headlink' => function($sl) {
                $cacheBustingManager = $sl->getServiceLocator()->get('AssetManager\Service\AssetCacheBustingManager');

                if ($cacheBustingManager->getOverrideHeadHelper()) {

                    return new AssetManager\Helper\HeadLink($sl);
                }

                return new Zend\View\Helper\HeadLink();
            },
            'headscript' => function($sl) {
                $cacheBustingManager = $sl->getServiceLocator()->get('AssetManager\Service\AssetCacheBustingManager');

                if ($cacheBustingManager->getOverrideHeadHelper()) {

                    return new AssetManager\Helper\HeadScript($sl);
                }

                return new Zend\View\Helper\HeadScript();
            }
        ),
    ),
    'asset_manager' => array(
        'resolvers' => array(
            'AssetManager\Resolver\MapResolver'                 => 2000,
            'AssetManager\Resolver\CollectionResolver'          => 1500,
            'AssetManager\Resolver\PrioritizedPathsResolver'    => 1000,
            'AssetManager\Resolver\PathStackResolver'           => 500,
        ),
    ),
);
