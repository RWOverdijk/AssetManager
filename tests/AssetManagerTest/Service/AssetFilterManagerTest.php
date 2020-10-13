<?php

namespace AssetManagerTest\Service;

use Assetic\Contracts\Asset\AssetInterface;
use Assetic\Asset\StringAsset;
use Assetic\Contracts\Filter\FilterInterface;
use AssetManager\Service\AssetFilterManager;
use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;

class AssetFilterManagerTest extends TestCase
{
    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        require_once __DIR__ . '/../../_files/CustomFilter.php';
    }

    public function testNulledValuesAreSkipped()
    {
        $assetFilterManager = new AssetFilterManager(array(
        'test/path.test' => array(
            'null_filters' => null
        )
        ));

        $asset = new StringAsset('Herp Derp');

        $assetFilterManager->setFilters('test/path.test', $asset);

        $this->assertEquals('Herp Derp', $asset->dump());
    }

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

        $asset = new StringAsset('Herp derp');

        $assetFilterManager->setFilters('test/path.test', $asset);

        $this->assertEquals('called', $asset->dump());
    }

    /**
     * @expectedException \RuntimeException
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

        $asset = new StringAsset('Herp derp');

        $assetFilterManager->setFilters('test/path.test', $asset);

        $this->assertEquals('called', $asset->dump());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testensureByInvalid()
    {
        $assetFilterManager = new AssetFilterManager(array(
            'test/path.test' => array(
                array(
                ),
            ),
        ));

        $asset = new StringAsset('Herp derp');

        $assetFilterManager->setFilters('test/path.test', $asset);
    }
    
    public function testFiltersAreInstantiatedOnce()
    {
        $assetFilterManager = new AssetFilterManager(array(
            'test/path.test' => array(
                array(
                    'filter' => 'CustomFilter'
                ),
            ),
        ));
        
        $filterInstance = null;
        
        $asset = $this->createMock(AssetInterface::class);
        $asset
            ->expects($this->any())
            ->method('ensureFilter')
            ->with($this->callback(function (FilterInterface $filter) use (&$filterInstance) {
                if ($filterInstance === null) {
                    $filterInstance = $filter;
                }
                $this->assertSame($filter, $filterInstance);

                return $filter === $filterInstance;
            }));
        
        $assetFilterManager->setFilters('test/path.test', $asset);
        $assetFilterManager->setFilters('test/path.test', $asset);
    }
}
