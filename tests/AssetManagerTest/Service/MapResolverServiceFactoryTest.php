<?php

namespace AssetManagerTest\Service;

use AssetManager\Resolver\MapResolver;
use AssetManager\Service\MapResolverServiceFactory;
use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;

class MapResolverServiceFactoryTest extends TestCase
{
    /**
     * Mainly to avoid regressions
     */
    public function testCreateService(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            array(
                'asset_manager' => array(
                    'resolver_configs' => array(
                        'map' => array(
                            'key1' => 'value1',
                            'key2' => 'value2',
                        ),
                    ),
                ),
            )
        );

        $factory = new MapResolverServiceFactory();
        /* @var MapResolver */
        $mapResolver = $factory->createService($serviceManager);
        $this->assertSame(
            array(
                'key1' => 'value1',
                'key2' => 'value2',
            ),
            $mapResolver->getMap()
        );
    }

    /**
     * Mainly to avoid regressions
     */
    public function testCreateServiceWithNoConfig(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('config', array());

        $factory = new MapResolverServiceFactory();
        /* @var MapResolver */
        $mapResolver = $factory->createService($serviceManager);
        $this->assertEmpty($mapResolver->getMap());
    }
}
