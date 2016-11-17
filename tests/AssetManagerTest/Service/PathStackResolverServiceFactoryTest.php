<?php

namespace AssetManagerTest\Service;

use AssetManager\Resolver\PathStackResolver;
use AssetManager\Service\PathStackResolverServiceFactory;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;

class PathStackResolverServiceFactoryTest extends PHPUnit_Framework_TestCase
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
                        'paths' => array(
                            'path1/',
                            'path2/',
                        ),
                    ),
                ),
            )
        );

        $factory = new PathStackResolverServiceFactory();
        /* @var $resolver PathStackResolver */
        $resolver = $factory->createService($serviceManager);
        $this->assertSame(
            array(
                'path2' . DIRECTORY_SEPARATOR,
                'path1' . DIRECTORY_SEPARATOR,
            ),
            $resolver->getPaths()->toArray()
        );
    }

    /**
     * Mainly to avoid regressions
     */
    public function testCreateServiceWithNoConfig()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('config', array());

        $factory = new PathStackResolverServiceFactory();
        /* @var $resolver PathStackResolver */
        $resolver = $factory->createService($serviceManager);
        $this->assertEmpty($resolver->getPaths()->toArray());
    }
}
