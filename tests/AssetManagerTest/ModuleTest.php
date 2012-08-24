<?php

namespace AssetManagerTest;

use PHPUnit_Framework_TestCase;
use AssetManager\Module;
use Zend\Http\Response;
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
     * Verifies that dispatch listener does nothing on other repsponse codes
     */
    public function testDispatchListenerIgnoresOtherResponseCodes()
    {
        $event = new MvcEvent();
        $response = new Response();
        $response->setStatusCode(500);
        $event->setResponse($response);

        $module = new Module();

        $response = $module->onDispatch($event);

        $this->assertNull($response);
    }
}
