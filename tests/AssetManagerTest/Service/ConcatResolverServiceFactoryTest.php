<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use AssetManager\Service\ConcatResolverServiceFactory;
use Zend\ServiceManager\ServiceManager;

class ConcatResolverServiceFactoryTest extends PHPUnit_Framework_TestCase
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
                         'concat' => array(
                             'key1' => __FILE__,
                             'key2' => __FILE__,
                         ),
                     ),
                 ),
            )
        );

        $factory = new ConcatResolverServiceFactory();
        /* @var \AssetManager\Resolver\CollectionResolver */
        $concatResolver = $factory->createService($serviceManager);
        $this->assertSame(
            array(
                 'key1' => __FILE__,
                 'key2' => __FILE__,
            ),
            $concatResolver->getConcats()
        );
    }

    /**
     * Mainly to avoid regressions
     */
    public function testCreateServiceWithNoConfig()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('Config', array());

        $factory = new ConcatResolverServiceFactory();
        /* @var \AssetManager\Resolver\ConcatResolver */
        $concatResolver = $factory->createService($serviceManager);
        $this->assertEmpty($concatResolver->getConcats());
    }
}
