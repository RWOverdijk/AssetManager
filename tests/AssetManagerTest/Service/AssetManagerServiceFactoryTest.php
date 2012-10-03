<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use AssetManager\Service\AssetManagerServiceFactory;
use Zend\ServiceManager\ServiceManager;

class AssetManagerServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $assetFilterManager = new \AssetManager\Service\AssetFilterManager();
        $assetCacheManager = new \AssetManager\Service\AssetCacheManager();
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'AssetManager\Service\AggregateResolver',
            $this->getMock('AssetManager\Resolver\ResolverInterface')
        );

        $serviceManager->setService(
            'AssetManager\Service\AssetFilterManager',
             $assetFilterManager
        );

        $serviceManager->setService(
            'AssetManager\Service\AssetCacheManager',
             $assetCacheManager
        );

        $serviceManager->setService('Config', array(
            'asset_manager' => array(
                'Dummy data',
                'Bacon',
            ),
        ));

        $factory = new AssetManagerServiceFactory();
        $this->assertInstanceOf('AssetManager\Service\AssetManager', $factory->createService($serviceManager));
    }
}
