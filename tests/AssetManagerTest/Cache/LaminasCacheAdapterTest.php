<?php

namespace AssetManagerTest\Cache;

use AssetManager\Cache\LaminasCacheAdapter;
use Laminas\Cache\Storage\Adapter\Memory;

/**
 * Test file for Laminas Cache Adapter
 *
 * @package AssetManager\Cache
 */
class LaminasCacheAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $mockLaminasCache = $this->getMockBuilder(Memory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $adapter = new LaminasCacheAdapter($mockLaminasCache);

        $this->assertInstanceOf(LaminasCacheAdapter::class, $adapter);
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testConstructorOnlyAcceptsALaminasCacheStorageInterface()
    {
        if (PHP_MAJOR_VERSION >= 7) {
            $this->setExpectedException('\TypeError');
        }

        new LaminasCacheAdapter(new \DateTime());
    }

    public function testHasMethodCallsLaminasCacheHasItem()
    {
        $mockLaminasCache = $this->getMockBuilder(Memory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockLaminasCache->expects($this->once())
            ->method('hasItem');

        $adapter = new LaminasCacheAdapter($mockLaminasCache);
        $adapter->has('SomeKey');
    }

    public function testGetMethodCallsLaminasCacheGetItem()
    {
        $mockLaminasCache = $this->getMockBuilder(Memory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockLaminasCache->expects($this->once())
            ->method('getItem');

        $adapter = new LaminasCacheAdapter($mockLaminasCache);
        $adapter->get('SomeKey');
    }

    public function testSetMethodCallsLaminasCacheSetItem()
    {
        $mockLaminasCache = $this->getMockBuilder(Memory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockLaminasCache->expects($this->once())
            ->method('setItem');

        $adapter = new LaminasCacheAdapter($mockLaminasCache);
        $adapter->set('SomeKey', array());
    }

    public function testRemoveMethodCallsLaminasCacheRemoveItem()
    {
        $mockLaminasCache = $this->getMockBuilder(Memory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockLaminasCache->expects($this->once())
            ->method('removeItem');

        $adapter = new LaminasCacheAdapter($mockLaminasCache);
        $adapter->remove('SomeKey');
    }
}
