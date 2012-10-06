<?php

namespace AssetManagerTest\Service;

require_once __DIR__ . '/../../_files/CustomFilter.php';

use PHPUnit_Framework_TestCase;
use AssetManager\Service\AssetFilterManager;
use Zend\ServiceManager\ServiceManager;

class AssetFilterManagerTest extends PHPUnit_Framework_TestCase
{
    public function testensureByService()
    {
        $assetFilterManager = new AssetFilterManager(array(
            'test/path.test' => array(
                array(
                    'service' => 'testFilter',
                ),
            ),
        ));

        $serviceManager = new ServiceManager();
        $serviceManager->setService('testFilter', new \CustomFilter());
        $assetFilterManager->setServiceLocator($serviceManager);

        $asset = new \Assetic\Asset\StringAsset('Herp derp');

        $assetFilterManager->setFilters('test/path.test', $asset);

        $this->assertEquals('called', $asset->dump());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testensureByServiceInvalid()
    {
        $assetFilterManager = new AssetFilterManager(array(
            'test/path.test' => array(
                array(
                    'service' => 9,
                ),
            ),
        ));

        $serviceManager = new ServiceManager();
        $serviceManager->setService('testFilter', new \CustomFilter());
        $assetFilterManager->setServiceLocator($serviceManager);

        $asset = new \Assetic\Asset\StringAsset('Herp derp');

        $assetFilterManager->setFilters('test/path.test', $asset);

        $this->assertEquals('called', $asset->dump());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testensureByInvalid()
    {
        $assetFilterManager = new AssetFilterManager(array(
            'test/path.test' => array(
                array(
                ),
            ),
        ));

        $asset = new \Assetic\Asset\StringAsset('Herp derp');

        $assetFilterManager->setFilters('test/path.test', $asset);
    }
}
