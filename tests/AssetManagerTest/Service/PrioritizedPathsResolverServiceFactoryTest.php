<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use AssetManager\Service\PrioritizedPathsResolverServiceFactory;
use Zend\ServiceManager\ServiceManager;

class PrioritizedPathsResolverServiceFactoryTest extends PHPUnit_Framework_TestCase
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
                        'prioritized_paths' => array(
                            array(
                                'path' => 'dir3',
                                'priority' => 750,
                            ),
                            array(
                                'path' => 'dir2',
                                'priority' => 1000,
                            ),
                            array(
                                'path' => 'dir1',
                                'priority' => 500,
                            ),
                        ),
                    ),
                ),
            )
        );

        $factory = new PrioritizedPathsResolverServiceFactory();
        /* @var $resolver \AssetManager\Resolver\PrioritizedPathsResolver */
        $resolver = $factory->createService($serviceManager);

        $fetched = array();

        foreach ($resolver->getPaths() as $path) {
            $fetched[] = $path;
        }

        $this->assertSame(
            array('dir2' . DIRECTORY_SEPARATOR, 'dir3' . DIRECTORY_SEPARATOR, 'dir1' . DIRECTORY_SEPARATOR),
            $fetched
        );
    }

    /**
     * Mainly to avoid regressions
     */
    public function testCreateServiceWithNoConfig()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('Config', array());

        $factory = new PrioritizedPathsResolverServiceFactory();
        /* @var $resolver \AssetManager\Resolver\PrioritizedPathsResolver */
        $resolver = $factory->createService($serviceManager);
        $this->assertEmpty($resolver->getPaths()->toArray());
    }
}
