<?php

namespace AssetManagerTest;

use PHPUnit_Framework_TestCase;
use AssetManager\Module;
use Zend\Http\Response;
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
