<?php

namespace AssetManagerTest\Cache;

use PHPUnit_Framework_TestCase;
use Assetic\Cache\CacheInterface;
use AssetManager\Cache\FilePathCache;

class FilePathCacheTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $cache = new FilePathCache('/imagination', 'bacon.porn');
        $this->assertTrue($cache instanceof CacheInterface);

        $this->assertAttributeEquals(
            '/imagination',
            'dir',
            $cache
        );

        $this->assertAttributeEquals(
            'bacon.porn',
            'filename',
            $cache
        );
    }

    public function testHas()
    {
        // Check fail
        $cache = new FilePathCache('/imagination', 'bacon.porn');
        $this->assertFalse($cache->has('bacon'));

        // Check success
        $cache = new FilePathCache('', __FILE__);
        $this->assertTrue($cache->has('bacon'));
    }

    /**
     * @expectedException \RunTimeException
     *
     */
    public function testGetException()
    {
        $cache = new FilePathCache('/imagination', 'bacon.porn');
        $cache->get('bacon');
    }

    public function testGet()
    {
        $cache = new FilePathCache('', __FILE__);
        $this->assertEquals(file_get_contents(__FILE__), $cache->get('bacon'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSetMayNotWriteFile()
    {
        restore_error_handler(); // Previous test fails, so doesn't unset.
        $time = time();
        $sentence = 'I am, what I am. Cached data, please don\'t hate, '
            . 'for we are all equals. Except you, you\'re a dick.';
        $base = '/tmp/_cachetest.' . $time . '/';
        mkdir($base, 0777);
        mkdir($base.'readonly', 0400, true);

        $cache = new FilePathCache($base.'readonly', 'bacon.'.$time.'.hammertime');
        $cache->set('bacon', $sentence);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSetMayNotWriteDir()
    {
        restore_error_handler(); // Previous test fails, so doesn't unset.
        $time = time()+1;
        $sentence = 'I am, what I am. Cached data, please don\'t hate, '
            . 'for we are all equals. Except you, you\'re a dick.';
        $base = '/tmp/_cachetest.' . $time . '/';
        mkdir($base, 0400, true);

        $cache = new FilePathCache($base.'readonly', 'bacon.'.$time.'.hammertime');

        $cache->set('bacon', $sentence);

    }

    public function testSetSuccess()
    {
        $time       = time();
        $sentence = 'I am, what I am. Cached data, please don\'t hate, '
            . 'for we are all equals. Except you, you\'re a dick.';
        $base       = '/tmp/_cachetest.' . $time . '/';
        $cache      = new FilePathCache($base, 'bacon.'.$time);

        $cache->set('bacon', $sentence);
        $this->assertEquals($sentence, file_get_contents($base.'bacon.'.$time));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testRemoveFails()
    {
        $cache = new FilePathCache('/dev', 'null');

        $cache->remove('bacon');
    }

    public function testRemoveSuccess()
    {
        $time       = time();
        $sentence = 'I am, what I am. Cached data, please don\'t hate, '
            . 'for we are all equals. Except you, you\'re a dick.';
        $base       = '/tmp/_cachetest.' . $time . '/';
        $cache      = new FilePathCache($base, 'bacon.'.$time);

        $cache->set('bacon', $sentence);

        $this->assertTrue($cache->remove('bacon'));
    }

    public function testCachedFile()
    {
        $method = new \ReflectionMethod('AssetManager\Cache\FilePathCache', 'cachedFile');

        $method->setAccessible(true);

        $this->assertEquals(
            '/' . ltrim(__FILE__, '/'),
            $method->invoke(new FilePathCache('', __FILE__))
        );
    }
}
