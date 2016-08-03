<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use AssetManager\Service\MapResolverServiceFactory;
use Zend\ServiceManager\ServiceManager;

class MapResolverServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * Mainly to avoid regressions
     */
    public function testCreateService()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Config',
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
        /* @var \AssetManager\Resolver\MapResolver */
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
    public function testCreateServiceWithNoConfig()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('Config', array());

        $factory = new MapResolverServiceFactory();
        /* @var \AssetManager\Resolver\MapResolver */
        $mapResolver = $factory->createService($serviceManager);
        $this->assertEmpty($mapResolver->getMap());
    }
}
