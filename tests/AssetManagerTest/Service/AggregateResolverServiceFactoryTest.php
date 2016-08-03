<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use AssetManager\Service\AssetFilterManager;
use AssetManager\Service\AggregateResolverServiceFactory;
use AssetManager\Service\MimeResolver;
use Zend\ServiceManager\ServiceManager;

class AggregateResolverServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        require_once __DIR__ . '/../../_files/InterfaceTestResolver.php';
    }

    public function testWillInstantiateEmptyResolver()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('Config', array());
        $serviceManager->setService('AssetManager\Service\MimeResolver', new MimeResolver);

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
        $serviceManager->setService('AssetManager\Service\MimeResolver', new MimeResolver);

        $factory = new AggregateResolverServiceFactory();
        $resolver = $factory->createService($serviceManager);

        $this->assertSame('test-resolved-path', $resolver->resolve('test-path'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInvalidCustomResolverFails()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Config',
            array(
                'asset_manager' => array(
                    'resolvers' => array(
                        'My\Resolver' => 1234,
                    ),
                ),
            )
        );
        $serviceManager->setService(
            'My\Resolver',
            new \stdClass
        );

        $factory = new AggregateResolverServiceFactory();
        $factory->createService($serviceManager);
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
        $serviceManager->setService('AssetManager\Service\MimeResolver', new MimeResolver);
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
        $serviceManager->setService('AssetManager\Service\MimeResolver', new MimeResolver);

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

    public function testWillSetForInterfaces()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Config',
            array(
                'asset_manager' => array(
                    'resolvers' => array(
                        'mocked_resolver' => 1000,
                    ),
                ),
            )
        );

        $interfaceTestResolver = new \InterfaceTestResolver;

        $serviceManager->setService('AssetManager\Service\MimeResolver', new MimeResolver);
        $serviceManager->setService('mocked_resolver', $interfaceTestResolver);
        $serviceManager->setService('AssetManager\Service\AssetFilterManager', new AssetFilterManager);

        $factory = new AggregateResolverServiceFactory();

        $factory->createService($serviceManager);

        $this->assertTrue($interfaceTestResolver->calledMime);
        $this->assertTrue($interfaceTestResolver->calledAggregate);
        $this->assertTrue($interfaceTestResolver->calledFilterManager);
    }
}
