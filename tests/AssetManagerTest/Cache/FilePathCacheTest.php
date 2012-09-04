<?php

namespace AssetManager\Cache;

use PHPUnit_Framework_TestCase;
use Assetic\Cache\CacheInterface;

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
     * @expectedException RunTimeException
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

    public function testSetMayNotWriteFile()
    {
        $time = time();
        $sentence = 'I am, what I am. Cached data, please don\'t hate, for we are all equals. Except you, you\'re a dick.';
        $base = '/tmp/_cachetest.' . $time . '/';
        mkdir($base, 0777);
        mkdir($base.'readonly', 0400, true);

        $cache = new FilePathCache($base.'readonly', 'bacon.'.$time.'.hammertime');
        try {
            $cache->set('bacon', $sentence);
        } catch (\Exception $e) {
            $error = "file_put_contents({$base}readonly/bacon.$time.hammertime): failed to open stream: Permission denied";
            $this->assertEquals($e->getMessage(), $error);
        }
    }

    public function testSetMayNotWriteDir()
    {
        $time = time()+1;
        $sentence = 'I am, what I am. Cached data, please don\'t hate, for we are all equals. Except you, you\'re a dick.';
        $base = '/tmp/_cachetest.' . $time . '/';
        mkdir($base, 0400, true);

        $cache = new FilePathCache($base.'readonly', 'bacon.'.$time.'.hammertime');

        try {
            $cache->set('bacon', $sentence);

        } catch (\Exception $e) {
            $error = "mkdir(): Permission denied";
            $this->assertEquals($e->getMessage(), $error);
        }
    }

    public function testSetSuccess()
    {
        $time       = time();
        $sentence   = 'I am, what I am. Cached data, please don\'t hate, for we are all equals. Except you, you\'re a dick.';
        $base       = '/tmp/_cachetest.' . $time . '/';
        $cache      = new FilePathCache($base, 'bacon.'.$time);

        $cache->set('bacon', $sentence);
        $this->assertEquals($sentence, file_get_contents($base.'bacon.'.$time));
    }

    public function testRemoveFails()
    {
        $time       = time()+2;
        $sentence   = 'I am, what I am. Cached data, please don\'t hate, for we are all equals. Except you, you\'re a dick.';
        $base       = '/tmp/_cachetest.' . $time . '/';
        $path       = $base.'bacon.'.$time;
        $cache      = new FilePathCache('/dev', 'null');

        try {
            $cache->remove('bacon');
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'unlink(/dev/null): Permission denied');
        }
    }

    public function testCachedFile()
    {
        $method = new \ReflectionMethod(
          'AssetManager\Cache\FilePathCache', 'cachedFile'
        );

        $method->setAccessible(true);

        $this->assertEquals(
          '/' . ltrim(__FILE__, '/'),
          $method->invoke(new FilePathCache('', __FILE__))
        );
    }
}