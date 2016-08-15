<?php

namespace AssetManagerTest\Service;

use AssetManager\Service\AssetFilterManager;
use AssetManager\Service\AssetFilterManagerServiceFactory;
use AssetManager\Service\MimeResolver;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;

class AssetFilterManagerServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
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
