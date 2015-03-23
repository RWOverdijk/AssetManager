<?php

namespace AssetManagerTest\Service;

use AssetManager\Service\GlobPathStackResolverServiceFactory;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;

class GlobPathStackResolverServiceFactoryTest extends PHPUnit_Framework_TestCase
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
                        'paths' => array(
                            'path1' . DIRECTORY_SEPARATOR,
                            'path2' . DIRECTORY_SEPARATOR,
                        ),
                    ),
                ),
            )
        );

        $factory = new GlobPathStackResolverServiceFactory();
        /* @var $resolver GlobPathStackResolverServiceFactory */
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
        $serviceManager->setService('Config', array());

        $factory = new GlobPathStackResolverServiceFactory();
        /* @var $resolver \AssetManager\Resolver\PathStackResolver */
        $resolver = $factory->createService($serviceManager);
        $this->assertEmpty($resolver->getPaths()->toArray());
    }
}
