<?php

namespace AssetManagerTest;

use PHPUnit_Framework_TestCase;
use AssetManager\Module;
use Zend\EventManager\EventInterface;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;
use Zend\Mvc\MvcEvent;

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
     * Verifies that all events are maintained when no asset is served
     */
    public function testOnBootstrapWillNotStopApplicationOnMissingAsset()
    {
        $assetManagerMock = $this->getMock('AssetManager\Service\AssetManager', array(), array(), '', false);
        $assetManagerMock
            ->expects($this->once())
            ->method('serveAsset')
            ->will($this->returnValue(false));
        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceManager
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($assetManagerMock));

        $callbackInvocationCount = 0;
        $callback = function() use (&$callbackInvocationCount) {
            $callbackInvocationCount += 1;
        };

        $applicationEventManager = new EventManager();
        $applicationEventManager->attach(MvcEvent::EVENT_ROUTE, $callback);
        $applicationEventManager->attach(MvcEvent::EVENT_DISPATCH, $callback);
        $applicationEventManager->attach(MvcEvent::EVENT_RENDER, $callback);
        $applicationEventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, $callback);
        $applicationEventManager->attach(MvcEvent::EVENT_FINISH, $callback);

        $application = $this->getMock('Zend\Mvc\ApplicationInterface');
        $application
            ->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($applicationEventManager));
        $application
            ->expects($this->once())
            ->method('getServiceManager')
            ->will($this->returnValue($serviceManager));
        $application
            ->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($this->getMock('Zend\Stdlib\RequestInterface')));

        $event = new Event();
        $event->setTarget($application);

        $module = new Module();
        $module->onBootstrap($event);

        $applicationEventManager->trigger(MvcEvent::EVENT_ROUTE);
        $applicationEventManager->trigger(MvcEvent::EVENT_DISPATCH);
        $applicationEventManager->trigger(MvcEvent::EVENT_RENDER);
        $applicationEventManager->trigger(MvcEvent::EVENT_DISPATCH_ERROR);
        $applicationEventManager->trigger(MvcEvent::EVENT_FINISH);

        $this->assertSame(5, $callbackInvocationCount);
    }

    /**
     * Verifies that all events are maintained when no asset is served
     */
    public function testOnBootstrapWillStopRelevantApplicationPartsOnMissingAsset()
    {
        $assetManagerMock = $this->getMock('AssetManager\Service\AssetManager', array(), array(), '', false);
        $assetManagerMock
            ->expects($this->once())
            ->method('serveAsset')
            ->will($this->returnValue(true));
        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceManager
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($assetManagerMock));

        $invokedCallbacks = array();
        $callback = function(EventInterface $e) use (&$invokedCallbacks) {
            $invokedCallbacks[] = $e->getName();
        };

        $applicationEventManager = new EventManager();
        $applicationEventManager->attach(MvcEvent::EVENT_ROUTE, $callback);
        $applicationEventManager->attach(MvcEvent::EVENT_DISPATCH, $callback);
        $applicationEventManager->attach(MvcEvent::EVENT_RENDER, $callback);
        $applicationEventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, $callback);
        $applicationEventManager->attach(MvcEvent::EVENT_FINISH, $callback);

        $application = $this->getMock('Zend\Mvc\ApplicationInterface');
        $application
            ->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($applicationEventManager));
        $application
            ->expects($this->once())
            ->method('getServiceManager')
            ->will($this->returnValue($serviceManager));
        $application
            ->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($this->getMock('Zend\Stdlib\RequestInterface')));

        $event = new Event();
        $event->setTarget($application);

        $module = new Module();
        $module->onBootstrap($event);

        $applicationEventManager->trigger(MvcEvent::EVENT_ROUTE);
        $applicationEventManager->trigger(MvcEvent::EVENT_DISPATCH);
        $applicationEventManager->trigger(MvcEvent::EVENT_RENDER);
        $applicationEventManager->trigger(MvcEvent::EVENT_DISPATCH_ERROR);
        $applicationEventManager->trigger(MvcEvent::EVENT_FINISH);

        $this->assertSame(array(MvcEvent::EVENT_FINISH), $invokedCallbacks);
    }
}
