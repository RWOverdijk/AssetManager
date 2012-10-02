<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use AssetManager\Service\AssetManagerServiceFactory;
use Zend\ServiceManager\ServiceManager;

class AssetManagerServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'AssetManager\Service\AggregateResolver',
            $this->getMock('AssetManager\Resolver\ResolverInterface')
        );

        $serviceManager->setService(
            'mime_resolver',
            $this->getMock('AssetManager\Service\MimeResolver')
        );

        $serviceManager->setService('Config', array());

        $factory = new AssetManagerServiceFactory();
        $this->assertInstanceOf('AssetManager\Service\AssetManager', $factory->createService($serviceManager));
    }
}
