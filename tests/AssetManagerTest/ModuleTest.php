<?php

namespace AssetManagerTest;

use PHPUnit_Framework_TestCase;
use AssetManager\Module;
use Zend\Http\Response;
use Zend\Http\Request;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;
use Zend\Mvc\MvcEvent;

/**
* @covers AssetManager\Module
*/
class ModuleTest extends PHPUnit_Framework_TestCase
{
    public function testGetAutoloaderConfig()
    {
        $module = new Module();
        // just testing ZF specification requirements
        $this->assertInternalType('array', $module->getAutoloaderConfig());
    }

    public function testGetConfig()
    {
        $module = new Module();
        // just testing ZF specification requirements
        $this->assertInternalType('array', $module->getConfig());
    }

    /**
     * Verifies that dispatch listener does nothing on other repsponse codes
     */
    public function testDispatchListenerIgnoresOtherResponseCodes()
    {
        $event      = new MvcEvent();
        $response   = new Response();
        $module     = new Module();

        $response->setStatusCode(500);
        $event->setResponse($response);

        $response = $module->onDispatch($event);

        $this->assertNull($response);
    }

    public function testOnDispatchDoesntResolveToAsset()
    {
        $resolver     = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $assetManager = $this->getMock('AssetManager\Service\AssetManager', array('resolvesToAsset'), array($resolver));
        $assetManager
            ->expects($this->once())
            ->method('resolvesToAsset')
            ->will($this->returnValue(false));

        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceManager
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($assetManager));

        $application = $this->getMock('Zend\Mvc\ApplicationInterface');
        $application
            ->expects($this->once())
            ->method('getServiceManager')
            ->will($this->returnValue($serviceManager));

        $event      = new MvcEvent();
        $response   = new Response();
        $request    = new Request();
        $module     = new Module();

        $event->setApplication($application);
        $response->setStatusCode(404);
        $event->setResponse($response);
        $event->setRequest($request);

        $return = $module->onDispatch($event);

        $this->assertNull($return);
    }

    public function testOnDispatchStatus200()
    {
        $resolver     = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $assetManager = $this->getMock('AssetManager\Service\AssetManager', array('resolvesToAsset', 'setAssetOnResponse'), array($resolver));
        $assetManager
            ->expects($this->once())
            ->method('resolvesToAsset')
            ->will($this->returnValue(true));


        $amResponse = new Response();
        $amResponse->setContent('bacon');

        $assetManager
            ->expects($this->once())
            ->method('setAssetOnResponse')
            ->will($this->returnValue($amResponse));

        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceManager
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($assetManager));

        $application = $this->getMock('Zend\Mvc\ApplicationInterface');
        $application
            ->expects($this->once())
            ->method('getServiceManager')
            ->will($this->returnValue($serviceManager));

        $event      = new MvcEvent();
        $response   = new Response();
        $request    = new Request();
        $module     = new Module();

        $event->setApplication($application);
        $response->setStatusCode(404);
        $event->setResponse($response);
        $event->setRequest($request);

        $return = $module->onDispatch($event);

        $this->assertEquals(200, $return->getStatusCode());
    }

    public function testOnDispatchModifiedSinceRequestWith304()
    {
        $event      = new MvcEvent();
        $request    = new Request();
        $module     = new Module();
        $response   = new Response();
        $response->setStatusCode(404);
        $time = 'Sat, 19 Jan 2013 16:25:03 GMT';
        $request->getHeaders()->addHeaderLine('If-Modified-Since', $time);

        $resolver     = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $assetManager = $this->getMock('AssetManager\Service\AssetManager', array('resolvesToAsset', 'setAssetOnResponse', 'resolve'), array($resolver));
        $assetManager
            ->expects($this->once())
            ->method('resolvesToAsset')
            ->will($this->returnValue(true));

        $asset = new \Assetic\Asset\StringAsset("foo");
        $asset->setLastModified(strtotime($time));
        $assetManager
            ->expects($this->once())
            ->method('resolve')
            ->will($this->returnValue($asset));

        $amResponse = new Response();
        $amResponse->setContent('bacon');

        $assetManager
            ->expects($this->exactly(0))
            ->method('setAssetOnResponse');


        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceManager
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($assetManager));

        $application = $this->getMock('Zend\Mvc\ApplicationInterface');
        $application
            ->expects($this->once())
            ->method('getServiceManager')
            ->will($this->returnValue($serviceManager));

        $event->setApplication($application);
        $event->setRequest($request);
        $event->setResponse($response);

        $return = $module->onDispatch($event);

        $this->assertSame(304, $return->getStatusCode());
    }

    public function testOnDispatchModifiedSinceRequestWith200()
    {
        $event      = new MvcEvent();
        $request    = new Request();
        $module     = new Module();
        $response   = new Response();
        $response->setStatusCode(404);
        $time = 'Sat, 19 Jan 2013 16:25:03 GMT';
        $request->getHeaders()->addHeaderLine('If-Modified-Since', $time);

        $resolver     = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $assetManager = $this->getMock('AssetManager\Service\AssetManager', array('resolvesToAsset', 'setAssetOnResponse', 'resolve'), array($resolver));
        $assetManager
            ->expects($this->once())
            ->method('resolvesToAsset')
            ->will($this->returnValue(true));

        $asset = new \Assetic\Asset\StringAsset("foo");
        $asset->setLastModified(strtotime($time) + 1);

        $assetManager
            ->expects($this->once())
            ->method('resolve')
            ->will($this->returnValue($asset));

        $amResponse = new Response();
        $amResponse->setContent('bacon');

        $assetManager
            ->expects($this->once())
            ->method('setAssetOnResponse')
            ->will($this->returnValue($amResponse));


        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceManager
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($assetManager));

        $application = $this->getMock('Zend\Mvc\ApplicationInterface');
        $application
            ->expects($this->once())
            ->method('getServiceManager')
            ->will($this->returnValue($serviceManager));

        $event->setApplication($application);
        $event->setRequest($request);
        $event->setResponse($response);

        $return = $module->onDispatch($event);

        $this->assertSame(200, $return->getStatusCode());
    }

    public function testOnDispatchNoneMatchRequestWith200()
    {
        $event      = new MvcEvent();
        $request    = new \Zend\Http\PhpEnvironment\Request();
        $module     = new Module();
        $response   = new Response();
        $cache      = new \AssetManager\Service\CacheController(
            array(
                'cache_control' => array(
                    'etag' => true,
                    'lifetime' => '5m'
                )
            )
        );
        $response->setStatusCode(404);
        $request->getHeaders()->addHeaderLine('If-None-Match', 'a-b-c');
        $request->setRequestUri('/foo');

        $resolver     = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $filter       = $this->getMock('AssetManager\Service\AssetFilterManager');
        $assetManager = $this->getMock('AssetManager\Service\AssetManager', array('resolvesToAsset', 'setAssetOnResponse', 'resolve', 'getAssetFilterManager'), array($resolver));
        $assetManager->setCacheController($cache);
        $assetManager
            ->expects($this->once())
            ->method('resolvesToAsset')
            ->will($this->returnValue(true));

        $asset = new \Assetic\Asset\StringAsset("foo");

        $assetManager
            ->expects($this->once())
            ->method('resolve')
            ->will($this->returnValue($asset));

        $amResponse = new Response();
        $amResponse->setContent('bacon');

        $assetManager
            ->expects($this->once())
            ->method('setAssetOnResponse')
            ->will($this->returnValue($amResponse));

        $assetManager
            ->expects($this->once())
            ->method('getAssetFilterManager')
            ->will($this->returnValue($filter));


        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceManager
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($assetManager));

        $application = $this->getMock('Zend\Mvc\ApplicationInterface');
        $application
            ->expects($this->once())
            ->method('getServiceManager')
            ->will($this->returnValue($serviceManager));

        $event->setApplication($application);
        $event->setRequest($request);
        $event->setResponse($response);

        $return = $module->onDispatch($event);

        $this->assertSame(200, $return->getStatusCode());
    }

    public function testOnDispatchNoneMatchRequestWith304()
    {
        $event      = new MvcEvent();
        $request    = new \Zend\Http\PhpEnvironment\Request();
        $module     = new Module();
        $response   = new Response();
        $cache      = $this->getMock('AssetManager\Service\CacheController', array('calculateEtag'), array(
            array(
                'cache_control' => array(
                    'etag' => true,
                    'lifetime' => '5m'
                )
            )));
        $cache->expects($this->once())->method('calculateEtag')->will($this->returnValue('a-b-c'));

        $response->setStatusCode(404);
        $request->getHeaders()->addHeaderLine('If-None-Match', 'a-b-c');
        $request->setRequestUri('/foo');

        $resolver     = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $filter       = $this->getMock('AssetManager\Service\AssetFilterManager');
        $assetManager = $this->getMock('AssetManager\Service\AssetManager', array('resolvesToAsset', 'setAssetOnResponse', 'resolve', 'getAssetFilterManager'), array($resolver));
        $assetManager->setCacheController($cache);
        $assetManager
            ->expects($this->once())
            ->method('resolvesToAsset')
            ->will($this->returnValue(true));

        $asset = new \Assetic\Asset\StringAsset("foo");

        $assetManager
            ->expects($this->once())
            ->method('resolve')
            ->will($this->returnValue($asset));

        $amResponse = new Response();
        $amResponse->setContent('bacon');

        $assetManager
            ->expects($this->exactly(0))
            ->method('setAssetOnResponse');

        $assetManager
            ->expects($this->once())
            ->method('getAssetFilterManager')
            ->will($this->returnValue($filter));


        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceManager
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($assetManager));

        $application = $this->getMock('Zend\Mvc\ApplicationInterface');
        $application
            ->expects($this->once())
            ->method('getServiceManager')
            ->will($this->returnValue($serviceManager));

        $event->setApplication($application);
        $event->setRequest($request);
        $event->setResponse($response);

        $return = $module->onDispatch($event);

        $this->assertSame(304, $return->getStatusCode());
    }

    public function testOnDispatchNoneMatchRequestWithCacheBusting304()
    {
        $event      = new MvcEvent();
        $request    = new \Zend\Http\PhpEnvironment\Request();
        $module     = new Module();
        $response   = new Response();
        $cache      = $this->getMock('AssetManager\Service\CacheController', array('calculateEtag'), array(
                array(
                    'cache_control' => array(
                        'etag' => true,
                        'lifetime' => '5m'
                    )
                )));
        $cache->expects($this->exactly(0))->method('calculateEtag');

        $response->setStatusCode(404);
        $request->getHeaders()->addHeaderLine('If-None-Match', 'a-b-c');
        $uri = new \Zend\Uri\Http('http://foo.bar/foo.js;AMa-b-c');
        $request->setUri($uri);

        $resolver     = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $filter       = $this->getMock('AssetManager\Service\AssetFilterManager');
        $assetManager = $this->getMock('AssetManager\Service\AssetManager', array('resolvesToAsset', 'setAssetOnResponse', 'resolve', 'getAssetFilterManager'), array($resolver));
        $assetManager->setCacheController($cache);
        $assetManager
            ->expects($this->exactly(0))
            ->method('resolvesToAsset');

        $asset = new \Assetic\Asset\StringAsset("foo");

        $assetManager
            ->expects($this->exactly(0))
            ->method('resolve');

        $amResponse = new Response();
        $amResponse->setContent('bacon');

        $assetManager
            ->expects($this->exactly(0))
            ->method('setAssetOnResponse');

        $assetManager
            ->expects($this->exactly(0))
            ->method('getAssetFilterManager');


        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceManager
            ->expects($this->exactly(0))
            ->method('get');

        $application = $this->getMock('Zend\Mvc\ApplicationInterface');
        $application
            ->expects($this->exactly(0))
            ->method('getServiceManager');

        $event->setApplication($application);
        $event->setRequest($request);
        $event->setResponse($response);

        $return = $module->onDispatch($event);

        $this->assertSame(304, $return->getStatusCode());
    }

    public function testOnDispatchNoneMatchRequestWithCacheBusting200()
    {
        $event      = new MvcEvent();
        $request    = new \Zend\Http\PhpEnvironment\Request();
        $module     = new Module();
        $response   = new Response();
        $cache      = $this->getMock('AssetManager\Service\CacheController', array('calculateEtag'), array(
                array(
                    'cache_control' => array(
                        'etag' => true,
                        'lifetime' => '5m'
                    )
                )));

        $response->setStatusCode(404);
        $uri = new \Zend\Uri\Http('http://foo.bar/foo.js;AMa-b-c');
        $request->setUri($uri);

        $resolver     = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $filter       = $this->getMock('AssetManager\Service\AssetFilterManager');
        $assetManager = $this->getMock('AssetManager\Service\AssetManager', array('resolvesToAsset', 'setAssetOnResponse'), array($resolver));
        $assetManager->setCacheController($cache);
        $assetManager
            ->expects($this->once())
            ->method('resolvesToAsset')
            ->will($this->returnValue(true));

        $asset = new \Assetic\Asset\StringAsset("foo");

        $amResponse = new Response();
        $amResponse->setContent('bacon');

        $assetManager
            ->expects($this->once())
            ->method('setAssetOnResponse')
            ->will($this->returnValue($amResponse));

        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceManager
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($assetManager));

        $application = $this->getMock('Zend\Mvc\ApplicationInterface');
        $application
            ->expects($this->once())
            ->method('getServiceManager')
            ->will($this->returnValue($serviceManager));

        $event->setApplication($application);
        $event->setRequest($request);
        $event->setResponse($response);

        $return = $module->onDispatch($event);

        $this->assertSame(200, $return->getStatusCode());
    }


    /**
     * @covers \AssetManager\Module::onDispatch
     */
    public function testWillIgnoreInvalidResponseType()
    {
        $cliResponse = $this->getMock('Zend\Console\Response', array(), array(), '', false);
        $mvcEvent   = $this->getMock('Zend\Mvc\MvcEvent');
        $module     = new Module();

        $cliResponse->expects($this->never())->method('getStatusCode');
        $mvcEvent->expects($this->once())->method('getResponse')->will($this->returnValue($cliResponse));

        $this->assertNull($module->onDispatch($mvcEvent));
    }

    public function testOnBootstrap()
    {
        $applicationEventManager = new EventManager();

        $application = $this->getMock('Zend\Mvc\ApplicationInterface');
        $application
            ->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($applicationEventManager));

        $event = new Event();
        $event->setTarget($application);

        $module = new Module();
        $module->onBootstrap($event);

        $dispatchListeners = $applicationEventManager->getListeners(MvcEvent::EVENT_DISPATCH);

        foreach ($dispatchListeners as $listener) {
            $metaData = $listener->getMetadata();
            $callback = $listener->getCallback();

            $this->assertEquals('onDispatch', $callback[1]);
            $this->assertEquals(-9999999, $metaData['priority']);
            $this->assertTrue($callback[0] instanceof Module);

        }

        $dispatchListeners = $applicationEventManager->getListeners(MvcEvent::EVENT_DISPATCH_ERROR);

        foreach ($dispatchListeners as $listener) {
            $metaData = $listener->getMetadata();
            $callback = $listener->getCallback();

            $this->assertEquals('onDispatch', $callback[1]);
            $this->assertEquals(-9999999, $metaData['priority']);
            $this->assertTrue($callback[0] instanceof Module);

        }
    }
}
