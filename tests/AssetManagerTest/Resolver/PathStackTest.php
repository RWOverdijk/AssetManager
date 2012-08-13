<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use AssetManager\Resolver\PathStack;

class PathStackTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $resolver = new PathStack();
        $this->assertEmpty($resolver->getPaths()->toArray());

        $resolver->addPaths(array(__DIR__));
        $this->assertEquals(array(__DIR__ . DIRECTORY_SEPARATOR), $resolver->getPaths()->toArray());

        $resolver->clearPaths();
        $this->assertEquals(array(), $resolver->getPaths()->toArray());
    }

    public function testMap()
    {
        $resolver = new PathStack();
        $resolver->addPath(__DIR__);

        $this->assertEquals(__FILE__, $resolver->resolve(basename(__FILE__)));
        $this->assertNull($resolver->resolve('i-do-not-exist.php'));
    }

    public function testLfiProtection()
    {
        $resolver = new PathStack();
        // should be on by default
        $this->assertTrue($resolver->isLfiProtectionOn());
        $resolver->addPath(__DIR__);

        $this->assertNull($resolver->resolve(
            '..' . DIRECTORY_SEPARATOR . basename(__DIR__) . DIRECTORY_SEPARATOR . basename(__FILE__)
        ));

        $resolver->setLfiProtection(false);

        $this->assertSame(
            __FILE__,
            $resolver->resolve(
                '..' . DIRECTORY_SEPARATOR . basename(__DIR__) . DIRECTORY_SEPARATOR . basename(__FILE__)
            )
        );
    }
}
