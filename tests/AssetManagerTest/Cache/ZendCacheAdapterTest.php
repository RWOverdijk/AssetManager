<?php

namespace AssetManagerTest\Cache;

use AssetManager\Cache\ZendCacheAdapter;
use Zend\Cache\Storage\Adapter\Memory;

/**
 * Test file for Zend Cache Adapter
 *
 * @package AssetManager\Cache
 */
class ZendCacheAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $mockZendCache = $this->getMockBuilder(Memory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $adapter = new ZendCacheAdapter($mockZendCache);

        $this->assertInstanceOf(ZendCacheAdapter::class, $adapter);
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testConstructorOnlyAcceptsAZendCacheStorageInterface()
    {
        if (PHP_MAJOR_VERSION >= 7) {
            $this->setExpectedException('\TypeError');
        }

        new ZendCacheAdapter(new \DateTime());
    }

    public function testHasMethodCallsZendCacheHasItem()
    {
        $mockZendCache = $this->getMockBuilder(Memory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockZendCache->expects($this->once())
            ->method('hasItem');

        $adapter = new ZendCacheAdapter($mockZendCache);
        $adapter->has('SomeKey');
    }

    public function testGetMethodCallsZendCacheGetItem()
    {
        $mockZendCache = $this->getMockBuilder(Memory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockZendCache->expects($this->once())
            ->method('getItem');

        $adapter = new ZendCacheAdapter($mockZendCache);
        $adapter->get('SomeKey');
    }

    public function testSetMethodCallsZendCacheSetItem()
    {
        $mockZendCache = $this->getMockBuilder(Memory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockZendCache->expects($this->once())
            ->method('setItem');

        $adapter = new ZendCacheAdapter($mockZendCache);
        $adapter->set('SomeKey', array());
    }

    public function testRemoveMethodCallsZendCacheRemoveItem()
    {
        $mockZendCache = $this->getMockBuilder(Memory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockZendCache->expects($this->once())
            ->method('removeItem');

        $adapter = new ZendCacheAdapter($mockZendCache);
        $adapter->remove('SomeKey');
    }
}
