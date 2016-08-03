<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use AssetManager\Service\PathStackResolverServiceFactory;
use Zend\ServiceManager\ServiceManager;

class PathStackResolverServiceFactoryTest extends PHPUnit_Framework_TestCase
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
                        'paths' => array(
                            'path1/',
                            'path2/',
                        ),
                    ),
                ),
            )
        );

        $factory = new PathStackResolverServiceFactory();
        /* @var $resolver \AssetManager\Resolver\PathStackResolver */
        $resolver = $factory($serviceManager);
        $this->assertSame(
            array(
                'path2/',
                'path1/',
            ),
            $resolver->getPaths()->toArray()
        );
    }

    /**
     * Mainly to avoid regressions
     */
    public function testInvokeWithNoConfig()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('config', array());

        $factory = new PathStackResolverServiceFactory();
        /* @var $resolver \AssetManager\Resolver\PathStackResolver */
        $resolver = $factory($serviceManager);
        $this->assertEmpty($resolver->getPaths()->toArray());
    }
}
