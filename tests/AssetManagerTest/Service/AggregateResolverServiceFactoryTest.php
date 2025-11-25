<?php

namespace AssetManagerTest\Service;

use AssetManager\Resolver\AggregateResolver;
use AssetManager\Resolver\ResolverInterface;
use AssetManager\Service\AggregateResolverServiceFactory;
use AssetManager\Service\AssetFilterManager;
use AssetManager\Service\MimeResolver;
use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;

class AggregateResolverServiceFactoryTest extends TestCase
{
    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        require_once __DIR__ . '/../../_files/InterfaceTestResolver.php';
    }

    public function testWillInstantiateEmptyResolver(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('config', array());
        $serviceManager->setService(MimeResolver::class, new MimeResolver);

        $factory = new AggregateResolverServiceFactory();
        $resolver = $factory->createService($serviceManager);
        $this->assertInstanceOf(ResolverInterface::class, $resolver);
        $this->assertNull($resolver->resolve('/some-path'));
    }

    public function testWillAttachResolver(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            array(
                'asset_manager' => array(
                    'resolvers' => array(
                        'mocked_resolver' => 1234,
                    ),
                ),
            )
        );

        $mockedResolver = $this->createMock(ResolverInterface::class);
        $mockedResolver
            ->expects($this->once())
            ->method('resolve')
            ->with('test-path')
            ->will($this->returnValue('test-resolved-path'));
        $serviceManager->setService('mocked_resolver', $mockedResolver);
        $serviceManager->setService(MimeResolver::class, new MimeResolver);

        $factory = new AggregateResolverServiceFactory();
        $resolver = $factory->__invoke($serviceManager, AggregateResolver::class);

        $this->assertSame('test-resolved-path', $resolver->resolve('test-path'));
    }

    public function testInvalidCustomResolverFails(): void
    {
        $this->expectException(\RuntimeException::class);
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
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
        $factory->__invoke($serviceManager, AggregateResolver::class);
    }

    public function testWillPrioritizeResolversCorrectly(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            array(
                'asset_manager' => array(
                    'resolvers' => array(
                        'mocked_resolver_1' => 1000,
                        'mocked_resolver_2' => 500,
                    ),
                ),
            )
        );

        $mockedResolver1 = $this->createMock(ResolverInterface::class);
        $mockedResolver1
            ->expects($this->once())
            ->method('resolve')
            ->with('test-path')
            ->will($this->returnValue('test-resolved-path'));
        $serviceManager->setService('AssetManager\Service\MimeResolver', new MimeResolver);
        $serviceManager->setService('mocked_resolver_1', $mockedResolver1);

        $mockedResolver2 = $this->createMock(ResolverInterface::class);
        $mockedResolver2
            ->expects($this->never())
            ->method('resolve');
        $serviceManager->setService('mocked_resolver_2', $mockedResolver2);

        $factory = new AggregateResolverServiceFactory();
        $resolver = $factory->createService($serviceManager);

        $this->assertSame('test-resolved-path', $resolver->resolve('test-path'));
    }

    public function testWillFallbackToLowerPriorityRoutes(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            array(
                'asset_manager' => array(
                    'resolvers' => array(
                        'mocked_resolver_1' => 1000,
                        'mocked_resolver_2' => 500,
                    ),
                ),
            )
        );

        $mockedResolver1 = $this->createMock(ResolverInterface::class);
        $mockedResolver1
            ->expects($this->once())
            ->method('resolve')
            ->with('test-path')
            ->will($this->returnValue(null));
        $serviceManager->setService('mocked_resolver_1', $mockedResolver1);
        $serviceManager->setService('AssetManager\Service\MimeResolver', new MimeResolver);

        $mockedResolver2 = $this->createMock(ResolverInterface::class);
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

    public function testWillSetForInterfaces(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            array(
                'asset_manager' => array(
                    'resolvers' => array(
                        'mocked_resolver' => 1000,
                    ),
                ),
            )
        );

        $interfaceTestResolver = new \InterfaceTestResolver;

        $serviceManager->setService(MimeResolver::class, new MimeResolver);
        $serviceManager->setService('mocked_resolver', $interfaceTestResolver);
        $serviceManager->setService(AssetFilterManager::class, new AssetFilterManager);

        $factory = new AggregateResolverServiceFactory();

        $factory->createService($serviceManager);

        $this->assertTrue($interfaceTestResolver->calledMime);
        $this->assertTrue($interfaceTestResolver->calledAggregate);
        $this->assertTrue($interfaceTestResolver->calledFilterManager);
    }
}
