<?php

namespace AssetManagerTest\Service;

use AssetManager\Resolver\CollectionResolver;
use AssetManager\Service\CollectionResolverServiceFactory;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;

class CollectionResolverServiceFactoryTest extends PHPUnit_Framework_TestCase
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
                        'collections' => array(
                            'key1' => 'value1',
                            'key2' => 'value2',
                        ),
                    ),
                ),
            )
        );

        $factory = new CollectionResolverServiceFactory();
        /* @var CollectionResolver */
        $collectionsResolver = $factory($serviceManager);
        $this->assertSame(
            array(
                'key1' => 'value1',
                'key2' => 'value2',
            ),
            $collectionsResolver->getCollections()
        );
    }

    /**
     * Mainly to avoid regressions
     */
    public function testInvokeWithNoConfig()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('config', array());

        $factory = new CollectionResolverServiceFactory();
        /* @var CollectionResolver */
        $collectionsResolver = $factory($serviceManager);
        $this->assertEmpty($collectionsResolver->getCollections());
    }
}
