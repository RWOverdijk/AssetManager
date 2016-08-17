<?php

namespace AssetManager\Service;

use Interop\Container\ContainerInterface;

class AssetFilterManagerServiceFactory
{
    /**
     * Build the Asset Filter Manager Service
     * 
     * @param ContainerInterface $container Container Service
     *
     * @return AssetFilterManager
     */
    public function __invoke(ContainerInterface $container)
    {
        $filters = array();
        $config  = $container->get('config');

        if (!empty($config['asset_manager']['filters'])) {
            $filters = $config['asset_manager']['filters'];
        }

        $assetFilterManager = new AssetFilterManager($filters);

        $assetFilterManager->setServiceLocator($container);
        $assetFilterManager->setMimeResolver($container->get(MimeResolver::class));

        return $assetFilterManager;
    }
}
