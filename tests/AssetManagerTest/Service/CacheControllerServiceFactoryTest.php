<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use AssetManager\Service\CacheControllerServiceFactory;
use AssetManager\Service\CacheController;
use AssetManager\Service\AggregateResolverServiceFactory;
use Zend\ServiceManager\ServiceManager;

class CacheControllerServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    protected $config = array();

    public function setUp()
    {
        $this->config = array(
            'asset_manager' => array(
                'cache_control' => array(
                    'lifetime' => '5m'
                )
            )
        );
    }

    public function testCorrectConfigInService()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('Config', $this->config);

        $factory = new CacheControllerServiceFactory();
        $cacheController = $factory->createService($serviceManager);
        $this->assertTrue($cacheController instanceof CacheController);
        $config = $cacheController->getConfig();
        $this->assertSame(array(
                'lifetime' => '5m'
            ),
            $config
        );
    }

    public function testForHeaderLineInResponse()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('Config', $this->config);

        $factory = new CacheControllerServiceFactory();
        $cacheController = $factory->createService($serviceManager);

        $response = $this->getMock('Zend\Http\Headers');
        $response->expects($this->once())->method('addHeaderLine')
            ->with(
                $this->equalTo('Cache-Control'),
                $this->equalTo('public, max-age=300')
        );
        $cacheController->addHeaders($response);
    }
}
