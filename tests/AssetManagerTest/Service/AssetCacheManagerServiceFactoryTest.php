<?php

namespace AssetManagerTest\Service;

use AssetManager\Service\AssetCacheManager;
use AssetManager\Service\AssetCacheManagerServiceFactory;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;

class AssetCacheManagerServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            array(
                'asset_manager' => array(
                    'caching' => array(
                        'default' => array(
                            'cache' => 'Apc',
                        ),
                    ),
                ),
            )
        );

        $assetManager = new AssetCacheManagerServiceFactory($serviceManager);
        $service = $assetManager($serviceManager);

        $this->assertTrue($service instanceof AssetCacheManager);
    }
}
