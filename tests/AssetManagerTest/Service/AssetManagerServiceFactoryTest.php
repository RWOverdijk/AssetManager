<?php

namespace AssetManagerTest\Service;

use AssetManager\Service\AssetCacheManager;
use AssetManager\Service\AssetFilterManager;
use PHPUnit_Framework_TestCase;
use AssetManager\Service\AssetManagerServiceFactory;
use Zend\ServiceManager\ServiceManager;

class AssetManagerServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $assetFilterManager = new AssetFilterManager();
        $assetCacheManager = $this->getMockBuilder('AssetManager\Service\AssetCacheManager')
            ->disableOriginalConstructor()
            ->getMock();

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
