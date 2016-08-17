<?php

namespace AssetManagerTest\Service;

use AssetManager\Resolver\PrioritizedPathsResolver;
use AssetManager\Service\PrioritizedPathsResolverServiceFactory;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;

class PrioritizedPathsResolverServiceFactoryTest extends PHPUnit_Framework_TestCase
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
        /* @var $resolver PrioritizedPathsResolver */
        $resolver = $factory($serviceManager);

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
    public function testInvokeWithNoConfig()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('config', array());

        $factory = new PrioritizedPathsResolverServiceFactory();
        /* @var $resolver PrioritizedPathsResolver */
        $resolver = $factory($serviceManager);
        $this->assertEmpty($resolver->getPaths()->toArray());
    }
}
