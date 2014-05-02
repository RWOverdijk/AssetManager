<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use AssetManager\Service\AssetCacheManagerServiceFactory;
use AssetManager\Service\AssetCacheManager      ;
use Zend\ServiceManager\ServiceManager;

class AssetCacheManagerServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Config',
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

        $service = $assetManager->createService($serviceManager);

        $this->assertTrue($service instanceof AssetCacheManager);
    }
}
