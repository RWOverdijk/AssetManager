<?php

namespace AssetManagerTest\Service;

use AssetManager\Resolver\MapResolver;
use AssetManager\Service\MapResolverServiceFactory;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;

class MapResolverServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * Mainly to avoid regressions
     */
    public function testInvoke()
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
        $mapResolver = $factory($serviceManager);
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
    public function testInvokeWithNoConfig()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('config', array());

        $factory = new MapResolverServiceFactory();
        /* @var MapResolver */
        $mapResolver = $factory($serviceManager);
        $this->assertEmpty($mapResolver->getMap());
    }
}
