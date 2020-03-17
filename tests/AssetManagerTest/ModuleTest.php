<?php

namespace AssetManagerTest;

use AssetManager\Module;
use AssetManager\Resolver\ResolverInterface;
use AssetManager\Service\AssetManager;
use PHPUnit\Framework\TestCase;
use Laminas\Console\Response as ConsoleResponse;
use Laminas\EventManager\Event;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\Test\EventListenerIntrospectionTrait;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\ApplicationInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
* @covers AssetManager\Module
*/
class ModuleTest extends TestCase
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
        $resolver     = $this->createMock(ResolverInterface::class);
        $assetManager = $this->createMock(
            AssetManager::class,
            array('resolvesToAsset'),
            array($resolver)
        );
        $assetManager
            ->expects($this->once())
            ->method('resolvesToAsset')
            ->will($this->returnValue(false));

        $serviceManager = $this->createMock(ServiceLocatorInterface::class);
        $serviceManager
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($assetManager));

        $application = $this->createMock(ApplicationInterface::class);
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
        $resolver     = $this->createMock(ResolverInterface::class);
        $assetManager = $this->createMock(
            AssetManager::class,
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

        $serviceManager = $this->createMock(ServiceLocatorInterface::class);
        $serviceManager
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($assetManager));

        $application = $this->createMock(ApplicationInterface::class);
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
        $cliResponse = $this->createMock(ConsoleResponse::class, array(), array(), '', false);
        $mvcEvent   = $this->createMock(MvcEvent::class);
        $module     = new Module();

        $cliResponse->expects($this->never())->method('getErrorLevel');
        $mvcEvent->expects($this->once())->method('getResponse')->will($this->returnValue($cliResponse));

        $this->assertNull($module->onDispatch($mvcEvent));
    }

    public function testOnBootstrap()
    {
        $applicationEventManager = new EventManager();

        $application = $this->createMock(ApplicationInterface::class);
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
