<?php

namespace AssetManagerTest;

use PHPUnit_Framework_TestCase;
use AssetManager\Module;
use Zend\EventManager\Test\EventListenerIntrospectionTrait;
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
    use EventListenerIntrospectionTrait;

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
        $resolver     = $this->getMock(\AssetManager\Resolver\ResolverInterface::class);
        $assetManager = $this->getMock(
            \AssetManager\Service\AssetManager::class,
            array('resolvesToAsset'),
            array($resolver)
        );
        $assetManager
            ->expects($this->once())
            ->method('resolvesToAsset')
            ->will($this->returnValue(false));

        $serviceManager = $this->getMock(\Zend\ServiceManager\ServiceLocatorInterface::class);
        $serviceManager
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($assetManager));

        $application = $this->getMock(\Zend\Mvc\ApplicationInterface::class);
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
        $resolver     = $this->getMock(\AssetManager\Resolver\ResolverInterface::class);
        $assetManager = $this->getMock(
            \AssetManager\Service\AssetManager::class,
            array('resolvesToAsset', 'setAssetOnResponse'),
            array($resolver)
        );
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

        $serviceManager = $this->getMock(\Zend\ServiceManager\ServiceLocatorInterface::class);
        $serviceManager
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($assetManager));

        $application = $this->getMock(\Zend\Mvc\ApplicationInterface::class);
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

    /**
     * @covers \AssetManager\Module::onDispatch
     */
    public function testWillIgnoreInvalidResponseType()
    {
        $cliResponse = $this->getMock(\Zend\Console\Response::class, array(), array(), '', false);
        $mvcEvent   = $this->getMock(\Zend\Mvc\MvcEvent::class);
        $module     = new Module();

        $cliResponse->expects($this->never())->method('getStatusCode');
        $mvcEvent->expects($this->once())->method('getResponse')->will($this->returnValue($cliResponse));

        $this->assertNull($module->onDispatch($mvcEvent));
    }

    public function testOnBootstrap()
    {
        $applicationEventManager = new EventManager();

        $application = $this->getMock(\Zend\Mvc\ApplicationInterface::class);
        $application
            ->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($applicationEventManager));

        $event = new Event();
        $event->setTarget($application);

        $module = new Module();
        $module->onBootstrap($event);

        $this->assertListenerAtPriority(
            [$module, 'onDispatch'],
            -9999999,
            MvcEvent::EVENT_DISPATCH,
            $applicationEventManager
        );

        $this->assertListenerAtPriority(
            [$module, 'onDispatch'],
            -9999999,
            MvcEvent::EVENT_DISPATCH_ERROR,
            $applicationEventManager
        );
    }
}
