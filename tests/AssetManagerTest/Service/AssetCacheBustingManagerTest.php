<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;

class AssetCacheBustingManagerTest extends PHPUnit_Framework_TestCase
{
    public function testCorrectConfigInterpretation()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Config',
            array(
                'asset_manager' => array(
                    'cache_busting' => array(
                        'enabled' => true,
                        'override_head_helper' => true
                    ),
                ),
            )
        );

        $tmp = new \AssetManager\Service\AssetCacheBustingManagerServiceFactory($serviceManager);
        $cacheBustingManager = $tmp->createService($serviceManager);

        $this->assertTrue($cacheBustingManager->isEnabled());
        $this->assertTrue($cacheBustingManager->getOverrideHeadHelper());
    }

    public function testDefaultSettings()
    {
        $cacheBustingManager = new \AssetManager\Service\AssetCacheBustingManager();

        $this->assertFalse($cacheBustingManager->isEnabled());
        $this->assertFalse($cacheBustingManager->getOverrideHeadHelper());
    }
}
