<?php

namespace AssetManagerTest\Cache;

use AssetManager\Cache\LaminasCacheAdapter;
use Laminas\Cache\Storage\Adapter\Memory;

/**
 * Test file for Laminas Cache Adapter
 *
 * @package AssetManager\Cache
 */
class LaminasCacheAdapterTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructor(): void
    {
        $mockLaminasCache = $this->getMockBuilder(Memory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $adapter = new LaminasCacheAdapter($mockLaminasCache);

        $this->assertInstanceOf(LaminasCacheAdapter::class, $adapter);
    }

    public function testConstructorOnlyAcceptsALaminasCacheStorageInterface(): void
    {
        $this->expectError();
        if (PHP_MAJOR_VERSION >= 7) {
            $this->expectException('\TypeError');
        }

        new LaminasCacheAdapter(new \DateTime());
    }

    public function testHasMethodCallsLaminasCacheHasItem(): void
    {
        $mockLaminasCache = $this->getMockBuilder(Memory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockLaminasCache->expects($this->once())
            ->method('hasItem');

        $adapter = new LaminasCacheAdapter($mockLaminasCache);
        $adapter->has('SomeKey');
    }

    public function testGetMethodCallsLaminasCacheGetItem(): void
    {
        $mockLaminasCache = $this->getMockBuilder(Memory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockLaminasCache->expects($this->once())
            ->method('getItem');

        $adapter = new LaminasCacheAdapter($mockLaminasCache);
        $adapter->get('SomeKey');
    }

    public function testSetMethodCallsLaminasCacheSetItem(): void
    {
        $mockLaminasCache = $this->getMockBuilder(Memory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockLaminasCache->expects($this->once())
            ->method('setItem');

        $adapter = new LaminasCacheAdapter($mockLaminasCache);
        $adapter->set('SomeKey', array());
    }

    public function testRemoveMethodCallsLaminasCacheRemoveItem(): void
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
