<?php

namespace AssetManagerTest\Service;

use AssetManager\Service\AssetFilterManager;
use AssetManager\Service\AssetFilterManagerServiceFactory;
use AssetManager\Service\MimeResolver;
use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;

class AssetFilterManagerServiceFactoryTest extends TestCase
{
    public function testConstruct(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            array(
                'asset_manager' => array(
                    'filters' => array(
                        'css' => array(
                            'filter' => 'Lessphp',
                        ),
                    ),
                ),
            )
        );

        $serviceManager->setService(MimeResolver::class, new MimeResolver);

        $t = new AssetFilterManagerServiceFactory($serviceManager);

        $service = $t->createService($serviceManager);

        $this->assertTrue($service instanceof AssetFilterManager);
    }
}
