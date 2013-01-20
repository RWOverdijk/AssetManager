<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use AssetManager\Service\CacheControllerServiceFactory;
use AssetManager\Service\CacheController;
use Assetic\Asset\StringAsset;
use Zend\ServiceManager\ServiceManager;

class CacheControllerServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    protected $config = array();

    public function setUp()
    {
        $this->config = array(
            'asset_manager' => array(
                'cache_control' => array(
                    'lifetime' => '5m',
                    'etag' => true
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
                'lifetime' => '5m',
                'etag' => true
            ),
            $config
        );

        $this->assertTrue($cacheController->hasEtag());
    }

    public function testDefaultEtagConfigInService()
    {
        $serviceManager = new ServiceManager();
        $conf = array( 'asset_manager' =>
            array(
                'cache_control' => array(
                    'lifetime' => '5m',
                    'magicetag' => true
                )
            )
        );
        $serviceManager->setService('Config', $conf);

        $factory = new CacheControllerServiceFactory();
        $cacheController = $factory->createService($serviceManager);
        $this->assertTrue($cacheController instanceof CacheController);
        $config = $cacheController->getConfig();
        $this->assertSame($conf, $conf);

        $this->assertFalse($cacheController->hasEtag());
        $this->assertTrue($cacheController->hasMagicEtag());

        $headers = new \Zend\Http\Headers();
        $cacheController->addHeaders($headers, new StringAsset('foo'));
        $this->assertFalse($headers->has('ETag'));
        $this->assertTrue($headers->has('Cache-Control'));
        $this->assertTrue($headers->has('Expires'));
    }

    public function testEtagCalculationOnlyExecuteOnce()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('Config', $this->config);

        $factory = new CacheControllerServiceFactory();
        $cacheController = $factory->createService($serviceManager);

        $asset = $this->getMock('Assetic\Asset\StringAsset', array('getLastModified'), array('foo'));
        $asset->expects($this->once())->method('getLastModified')->will($this->returnValue('Sat,19 Jan 2013 20:42:52 CET'));
        $cacheController->calculateEtag($asset);
        $cacheController->calculateEtag($asset);
    }

    public function testGetLifeTimeCalculation()
    {
        $serviceManager = new ServiceManager();
        $conf = array( 'asset_manager' =>
        array(
            'cache_control' => array(
                'lifetime' => '5m',
                'magicetag' => true
            )
        )
        );
        $serviceManager->setService('Config', $conf);

        $factory = new CacheControllerServiceFactory();
        $cacheController = $factory->createService($serviceManager);

        $this->assertSame(300, $cacheController->getLifetime());
        $conf = array();
        $conf['lifetime'] = '10m';
        $cacheController->setConfig($conf);
        $this->assertSame(600, $cacheController->getLifetime());

        $conf['lifetime'] = "1h";
        $cacheController->setConfig($conf);
        $this->assertSame(3600, $cacheController->getLifetime());

        $conf['lifetime'] = '2h';
        $cacheController->setConfig($conf);
        $this->assertSame(7200, $cacheController->getLifetime());

        $conf['lifetime'] = '1d';
        $cacheController->setConfig($conf);
        $this->assertSame(86400, $cacheController->getLifetime());

        $conf['lifetime'] = '2d';
        $cacheController->setConfig($conf);
        $this->assertSame(172800, $cacheController->getLifetime());
    }

    /**
     * @expectedException AssetManager\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid format
     */
    public function testGetLifeTimeCalculationWithInvalidParameter()
    {
        $serviceManager = new ServiceManager();
        $conf = array( 'asset_manager' =>
        array(
            'cache_control' => array(
                'lifetime' => 'foo',
                'magicetag' => true
            )
        )
        );
        $serviceManager->setService('Config', $conf);

        $factory = new CacheControllerServiceFactory();
        $cacheController = $factory->createService($serviceManager);

        $this->assertSame(300, $cacheController->getLifetime());
    }

    /**
     * @expectedException AssetManager\Exception\InvalidArgumentException
     * @expectedExceptionMessage Valid formatters are d,h,m
     */
    public function testGetLifeTimeCalculationWithInvalidFormatter()
    {
        $serviceManager = new ServiceManager();
        $conf = array( 'asset_manager' =>
        array(
            'cache_control' => array(
                'lifetime' => '1q',
                'magicetag' => true
            )
        )
        );
        $serviceManager->setService('Config', $conf);

        $factory = new CacheControllerServiceFactory();
        $cacheController = $factory->createService($serviceManager);

        $this->assertSame(300, $cacheController->getLifetime());
    }

    public function testForHeaderLineInResponse()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('Config', $this->config);

        $factory = new CacheControllerServiceFactory();
        $cacheController = $factory->createService($serviceManager);

        $response = $this->getMock('Zend\Http\Headers');
        $response->expects($this->any())->method('addHeaderLine');

        $cacheController->addHeaders($response, new StringAsset("foo"));
    }
}
