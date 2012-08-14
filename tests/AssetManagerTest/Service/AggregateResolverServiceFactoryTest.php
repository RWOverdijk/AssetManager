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

    public function testWillInstantiateMapResolver()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Config',
            array(
                'asset_manager' => array(
                    'map' => array(
                        '/some-path' => 'awesome-asset.cool',
                    ),
                ),
            )
        );

        $factory = new AggregateResolverServiceFactory();
        $resolver = $factory->createService($serviceManager);
        $this->assertInstanceOf('AssetManager\Resolver\ResolverInterface', $resolver);
        $this->assertSame('awesome-asset.cool', $resolver->resolve('/some-path'));
        $this->assertNull($resolver->resolve('/i-do-not-exist'));
    }

    public function testWillInstantiatePathStackResolver()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Config',
            array(
                'asset_manager' => array(
                    'paths' => array(
                        __DIR__,
                    ),
                ),
            )
        );

        $factory = new AggregateResolverServiceFactory();
        $resolver = $factory->createService($serviceManager);
        $this->assertInstanceOf('AssetManager\Resolver\ResolverInterface', $resolver);
        $this->assertSame(__FILE__, $resolver->resolve(basename(__FILE__)));
        $this->assertNull($resolver->resolve('/i-do-not-exist'));
    }

    public function testWillPrioritizeMapResolver()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Config',
            array(
                'asset_manager' => array(
                    'map' => array(
                        basename(__FILE__) => 'i-will-win',
                    ),
                    'paths' => array(
                        __DIR__,
                    ),
                ),
            )
        );

        $factory = new AggregateResolverServiceFactory();
        $resolver = $factory->createService($serviceManager);
        $this->assertInstanceOf('AssetManager\Resolver\ResolverInterface', $resolver);
        $this->assertSame('i-will-win', $resolver->resolve(basename(__FILE__)));
        $this->assertNull($resolver->resolve('/i-do-not-exist'));
    }
}
