<?php

namespace AssetManagerTest\Service;

use AssetManager\Resolver\CollectionResolver;
use AssetManager\Resolver\ConcatResolver;
use AssetManager\Service\ConcatResolverServiceFactory;
use PHPUnit_Framework_TestCase;
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
            'config',
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
        /* @var CollectionResolver */
        $concatResolver = $factory($serviceManager);
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
        $serviceManager->setService('config', array());

        $factory = new ConcatResolverServiceFactory();
        /* @var ConcatResolver */
        $concatResolver = $factory($serviceManager);
        $this->assertEmpty($concatResolver->getConcats());
    }
}
