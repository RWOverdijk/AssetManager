<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use AssetManager\Service\AggregateResolverServiceFactory;
use Zend\ServiceManager\ServiceManager;

class AggregateResolverServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testWillInstantiateEmptyResolver()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('Config', array());

        $factory = new AggregateResolverServiceFactory();
        $resolver = $factory->createService($serviceManager);
        $this->assertInstanceOf('AssetManager\Resolver\ResolverInterface', $resolver);
        $this->assertNull($resolver->resolve('/some-path'));
    }

    public function testWillAttachResolver()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Config',
            array(
                'asset_manager' => array(
                    'resolvers' => array(
                        'mocked_resolver' => 1234,
                    ),
                ),
            )
        );

        $mockedResolver = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $mockedResolver
            ->expects($this->once())
            ->method('resolve')
            ->with('test-path')
            ->will($this->returnValue('test-resolved-path'));
        $serviceManager->setService('mocked_resolver', $mockedResolver);

        $factory = new AggregateResolverServiceFactory();
        $resolver = $factory->createService($serviceManager);

        $this->assertSame('test-resolved-path', $resolver->resolve('test-path'));
    }

    public function testWillPrioritizeResolversCorrectly()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Config',
            array(
                'asset_manager' => array(
                    'resolvers' => array(
                        'mocked_resolver_1' => 1000,
                        'mocked_resolver_2' => 500,
                    ),
                ),
            )
        );

        $mockedResolver1 = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $mockedResolver1
            ->expects($this->once())
            ->method('resolve')
            ->with('test-path')
            ->will($this->returnValue('test-resolved-path'));
        $serviceManager->setService('mocked_resolver_1', $mockedResolver1);

        $mockedResolver2 = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $mockedResolver2
            ->expects($this->never())
            ->method('resolve');
        $serviceManager->setService('mocked_resolver_2', $mockedResolver2);

        $factory = new AggregateResolverServiceFactory();
        $resolver = $factory->createService($serviceManager);

        $this->assertSame('test-resolved-path', $resolver->resolve('test-path'));
    }

    public function testWillFallbackToLowerPriorityRoutes()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Config',
            array(
                'asset_manager' => array(
                    'resolvers' => array(
                        'mocked_resolver_1' => 1000,
                        'mocked_resolver_2' => 500,
                    ),
                ),
            )
        );

        $mockedResolver1 = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $mockedResolver1
            ->expects($this->once())
            ->method('resolve')
            ->with('test-path')
            ->will($this->returnValue(null));
        $serviceManager->setService('mocked_resolver_1', $mockedResolver1);

        $mockedResolver2 = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $mockedResolver2
            ->expects($this->once())
            ->method('resolve')
            ->with('test-path')
            ->will($this->returnValue('test-resolved-path'));
        $serviceManager->setService('mocked_resolver_2', $mockedResolver2);

        $factory = new AggregateResolverServiceFactory();
        $resolver = $factory->createService($serviceManager);

        $this->assertSame('test-resolved-path', $resolver->resolve('test-path'));
    }
}
