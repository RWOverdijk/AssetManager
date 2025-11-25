<?php

namespace AssetManagerTest\Service;

use AssetManager\Resolver\PathStackResolver;
use AssetManager\Service\PathStackResolverServiceFactory;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

class PathStackResolverServiceFactoryTest extends TestCase
{
    /**
     * Mainly to avoid regressions
     */
    public function testCreateService(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                'asset_manager' => [
                    'resolver_configs' => [
                        'paths' => [
                            'path1' . DIRECTORY_SEPARATOR,
                            'path2' . DIRECTORY_SEPARATOR,
                        ],
                    ],
                ],
            ],
        );

        $factory = new PathStackResolverServiceFactory();
        /* @var $resolver PathStackResolver */
        $resolver = $factory->__invoke($serviceManager, \AssetManager\Resolver\PathStackResolver::class);
        $this->assertSame(
            [
                'path2' . DIRECTORY_SEPARATOR,
                'path1' . DIRECTORY_SEPARATOR,
            ],
            $resolver->getPaths()->toArray(),
        );
    }

    /**
     * Mainly to avoid regressions
     */
    public function testCreateServiceWithNoConfig(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('config', []);

        $factory = new PathStackResolverServiceFactory();
        /* @var $resolver PathStackResolver */
        $resolver = $factory->__invoke($serviceManager, \AssetManager\Resolver\PathStackResolver::class);
        $this->assertEmpty($resolver->getPaths()->toArray());
    }
}
