<?php

namespace AssetManagerTest\Cache;

use AssetManager\Cache\PsrSimpleCacheAdapter;
use Psr\SimpleCache\CacheInterface;

/**
 * Test file for Laminas Cache Adapter
 *
 * @package AssetManager\Cache
 */
class PsrSimpleCacheAdapterTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructor(): void
    {
        $mockPsrCache = $this->getMockBuilder(CacheInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $adapter = new PsrSimpleCacheAdapter($mockPsrCache);

        $this->assertInstanceOf(PsrSimpleCacheAdapter::class, $adapter);
    }

    public function testHasMethodCallsPsrCacheHas(): void
    {
        $mockLaminasCache = $this->getMockBuilder(CacheInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockLaminasCache->expects($this->once())
            ->method('has');

        $adapter = new PsrSimpleCacheAdapter($mockLaminasCache);
        $adapter->has('SomeKey');
    }

    public function testGetMethodCallsPsrCacheGet(): void
    {
        $mockLaminasCache = $this->getMockBuilder(CacheInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockLaminasCache->expects($this->once())
            ->method('get')
            ->with('SomeKey');

        $adapter = new PsrSimpleCacheAdapter($mockLaminasCache);
        $adapter->get('SomeKey');
    }

    public function testSetMethodCallsPsrCacheSet(): void
    {
        $mockLaminasCache = $this->getMockBuilder(CacheInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockLaminasCache->expects($this->once())
            ->method('set')
            ->with('SomeKey', [], null);

        $adapter = new PsrSimpleCacheAdapter($mockLaminasCache);
        $adapter->set('SomeKey', array());
    }

    public function testSetMethodCallsPsrCacheSetWithTTL(): void
    {
        $mockLaminasCache = $this->getMockBuilder(CacheInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockLaminasCache->expects($this->once())
            ->method('set')
            ->with('SomeKey', [], 10);

        $adapter = new PsrSimpleCacheAdapter($mockLaminasCache, 10);
        $adapter->set('SomeKey', array());
    }

    public function testRemoveMethodCallsPsrCacheRemove(): void
    {
        $mockLaminasCache = $this->getMockBuilder(CacheInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockLaminasCache->expects($this->once())
            ->method('delete')
            ->with('SomeKey');

        $adapter = new PsrSimpleCacheAdapter($mockLaminasCache);
        $adapter->remove('SomeKey');
    }
}
