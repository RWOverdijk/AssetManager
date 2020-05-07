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
    public function testConstructor()
    {
        $mockPsrCache = $this->getMockBuilder(CacheInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $adapter = new PsrSimpleCacheAdapter($mockPsrCache);

        $this->assertInstanceOf(PsrSimpleCacheAdapter::class, $adapter);
    }

    public function testHasMethodCallsPsrCacheHas()
    {
        $mockLaminasCache = $this->getMockBuilder(CacheInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockLaminasCache->expects($this->once())
            ->method('has');

        $adapter = new PsrSimpleCacheAdapter($mockLaminasCache);
        $adapter->has('SomeKey');
    }

    public function testGetMethodCallsPsrCacheGet()
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

    public function testSetMethodCallsPsrCacheSet()
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

    public function testSetMethodCallsPsrCacheSetWithTTL()
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

    public function testRemoveMethodCallsPsrCacheRemove()
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
