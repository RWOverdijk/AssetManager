<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use AssetManager\Service\AssetFilterManagerServiceFactory;
use AssetManager\Service\AssetFilterManager;
use AssetManager\Service\MimeResolver;
use Zend\ServiceManager\ServiceManager;

class AssetFilterManagerServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Config',
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

        $serviceManager->setService('mime_resolver', new MimeResolver);

        $t = new AssetFilterManagerServiceFactory($serviceManager);

        $service = $t->createService($serviceManager);

        $this->assertTrue($service instanceof AssetFilterManager);
    }
}
