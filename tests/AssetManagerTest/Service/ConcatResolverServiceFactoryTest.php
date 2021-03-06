<?php

namespace AssetManagerTest\Service;

use AssetManager\Resolver\CollectionResolver;
use AssetManager\Resolver\ConcatResolver;
use AssetManager\Service\ConcatResolverServiceFactory;
use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;

class ConcatResolverServiceFactoryTest extends TestCase
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
    public function testCreateServiceWithNoConfig(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('config', array());

        $factory = new ConcatResolverServiceFactory();
        /* @var ConcatResolver */
        $concatResolver = $factory->createService($serviceManager);
        $this->assertEmpty($concatResolver->getConcats());
    }
}
