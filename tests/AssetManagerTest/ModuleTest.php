<?php

namespace AssetManagerTest;

use PHPUnit_Framework_TestCase;
use AssetManager\Module;
use Zend\Diactoros\Response;
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
        $response   = new \Zend\Http\Response();
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
        $response   = new \Zend\Http\Response();
        $request    = new \Zend\Http\PhpEnvironment\Request();
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
        $assetManager = $this->getMock(
            'AssetManager\Service\AssetManager',
            array('resolvesToAsset', 'setAssetOnResponse'),
            array($resolver)
        );
        $assetManager
            ->expects($this->once())
            ->method('resolvesToAsset')
            ->will($this->returnValue(true));


        $amResponse = new Response();
        $amResponse->getBody()->write('bacon');

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
        $response   = new \Zend\Http\Response();
        $request    = new \Zend\Http\PhpEnvironment\Request();
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
        $cliResponse = $this->getMock('Zend\Console\Response', array(), array(), '', false);
        $mvcEvent   = $this->getMock('Zend\Mvc\MvcEvent');
        $module     = new Module();

        $cliResponse->expects($this->never())->method('getStatusCode');
        $mvcEvent->expects($this->once())->method('getResponse')->will($this->returnValue($cliResponse));

        $this->assertNull($module->onDispatch($mvcEvent));
    }

    public function testOnBootstrap()
    {
        $module = new Module();

        $applicationEventManager = $this->getMockBuilder('Zend\EventManager\EventManager')
            ->disableOriginalConstructor()
            ->getMock();
        
        $applicationEventManager->expects($this->exactly(2))
            ->method('attach')
            ->withConsecutive(
                [MvcEvent::EVENT_DISPATCH, array($module, 'onDispatch'), -9999999],
                [MvcEvent::EVENT_DISPATCH_ERROR, array($module, 'onDispatch'), -9999999]
            );

        $application = $this->getMock('Zend\Mvc\ApplicationInterface');
        $application
            ->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($applicationEventManager));

        $event = new Event();
        $event->setTarget($application);


        $module->onBootstrap($event);
    }
}
