<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use ArrayObject;
use AssetManager\Resolver\PathStackResolver;
use AssetManager\Resolver\MimeResolverAwareInterface;
use AssetManager\Service\MimeResolver;

class PathStackResolverTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $resolver = new PathStackResolver();
        $this->assertEmpty($resolver->getPaths()->toArray());

        $resolver->addPaths(array(__DIR__));
        $this->assertEquals(array(__DIR__ . DIRECTORY_SEPARATOR), $resolver->getPaths()->toArray());

        $resolver->clearPaths();
        $this->assertEquals(array(), $resolver->getPaths()->toArray());
    }

   public function testSetPaths()
    {
        $resolver = new PathStackResolver();
        $resolver->setPaths(array('dir2', 'dir1'));
        // order inverted because of how a stack is traversed
        $this->assertSame(
            array('dir1' . DIRECTORY_SEPARATOR, 'dir2' . DIRECTORY_SEPARATOR),
            $resolver->getPaths()->toArray()
        );

        $paths = new ArrayObject(array(
            'dir4',
            'dir3',
        ));
        $resolver->setPaths($paths);
        $this->assertSame(
            array('dir3' . DIRECTORY_SEPARATOR, 'dir4' . DIRECTORY_SEPARATOR),
            $resolver->getPaths()->toArray()
        );

        $this->setExpectedException('AssetManager\Exception\InvalidArgumentException');
        $resolver->setPaths('invalid');

    }

    public function testResolve()
    {
        $resolver = new PathStackResolver();
        $this->assertTrue($resolver instanceOf PathStackResolver);

        $mimeResolver = new MimeResolver;
        $resolver->setMimeResolver($mimeResolver);

        $resolver->addPath(__DIR__);

        $this->assertEquals(__FILE__, $resolver->resolve(basename(__FILE__)));
        $this->assertNull($resolver->resolve('i-do-not-exist.php'));
    }
/*
    public function testWillNotResolveDirectories()
    {
        $resolver = new PathStackResolver();
        $resolver->addPath(__DIR__ . '/..');

        $this->assertNull($resolver->resolve(basename(__DIR__)));
    }

    public function testLfiProtection()
    {
        $resolver = new PathStackResolver();
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

    public function testWillRefuseInvalidPath()
    {
        $resolver = new PathStackResolver();
        $this->setExpectedException('AssetManager\Exception\InvalidArgumentException');
        $resolver->addPath(null);
    }
*/
}
