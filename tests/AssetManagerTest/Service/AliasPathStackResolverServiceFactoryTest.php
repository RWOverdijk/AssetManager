<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use AssetManager\Service\AliasPathStackResolverServiceFactory;
use Zend\ServiceManager\ServiceManager;

/**
 * Unit Tests the factory for the Alias Path Stack Resolver
 */
class AliasPathStackResolverServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * Mainly to avoid regressions
     *
     * @covers \AssetManager\Service\AliasPathStackResolverServiceFactory
     */
    public function testCreateService()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Config',
            array(
                'asset_manager' => array(
                    'resolver_configs' => array(
                        'aliases' => array(
                            'alias1/' => 'path1',
                            'alias2/' => 'path2',
                        ),
                    ),
                ),
            )
        );

        $factory = new AliasPathStackResolverServiceFactory();

        /* @var $resolver \AssetManager\Resolver\AliasPathStackResolver */
        $resolver = $factory->createService($serviceManager);

        $reflectionClass = new \ReflectionClass('AssetManager\Resolver\AliasPathStackResolver');
        $property = $reflectionClass->getProperty('aliases');
        $property->setAccessible(true);

        $this->assertSame(
            array(
                'alias1/' => 'path1' . DIRECTORY_SEPARATOR,
                'alias2/' => 'path2' . DIRECTORY_SEPARATOR,
            ),
            $property->getValue($resolver)
        );
    }

    /**
     * Mainly to avoid regressions
     *
     * @covers \AssetManager\Service\AliasPathStackResolverServiceFactory
     */
    public function testCreateServiceWithNoConfig()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('Config', array());

        $factory = new AliasPathStackResolverServiceFactory();
        /* @var $resolver \AssetManager\Resolver\AliasPathStackResolver */
        $resolver = $factory->createService($serviceManager);

        $reflectionClass = new \ReflectionClass('AssetManager\Resolver\AliasPathStackResolver');
        $property = $reflectionClass->getProperty('aliases');
        $property->setAccessible(true);

        $this->assertEmpty($property->getValue($resolver));
    }
}
