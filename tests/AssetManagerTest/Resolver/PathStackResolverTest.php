<?php

namespace AssetManagerTest\Service;

use ArrayObject;
use Assetic\Asset;
use AssetManager\Exception\InvalidArgumentException;
use AssetManager\Resolver\MimeResolverAwareInterface;
use AssetManager\Resolver\PathStackResolver;
use AssetManager\Resolver\ResolverInterface;
use AssetManager\Service\MimeResolver;
use PHPUnit\Framework\TestCase;

class PathStackResolverTest extends TestCase
{
    public function testConstructor(): void
    {
        $resolver = new PathStackResolver();
        $this->assertEmpty($resolver->getPaths()->toArray());

        $resolver->addPaths(array(__DIR__));
        $this->assertEquals(array(__DIR__ . DIRECTORY_SEPARATOR), $resolver->getPaths()->toArray());

        $resolver->clearPaths();
        $this->assertEquals(array(), $resolver->getPaths()->toArray());

        $this->assertTrue($resolver instanceof MimeResolverAwareInterface);
        $this->assertTrue($resolver instanceof ResolverInterface);
        $mimeResolver = new MimeResolver;

        $resolver->setMimeResolver($mimeResolver);

        $this->assertEquals($mimeResolver, $resolver->getMimeResolver());
    }

    public function testSetMimeResolverFailObject(): void
    {
        $this->expectError();
        $this->expectError();
        if (PHP_MAJOR_VERSION >= 7) {
            $this->expectException('\TypeError');
        }

        $resolver = new PathStackResolver();
        $resolver->setMimeResolver(new \stdClass());
    }

    public function testSetMimeResolverFailString(): void
    {
        $this->expectError();
        if (PHP_MAJOR_VERSION >= 7) {
            $this->expectException('\TypeError');
        }

        $resolver = new PathStackResolver();
        $resolver->setMimeResolver('invalid');
    }

    public function testSetPaths(): void
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

        $this->expectException(InvalidArgumentException::class);
        $resolver->setPaths('invalid');
    }

    public function testResolve(): void
    {
        $resolver = new PathStackResolver();
        $this->assertTrue($resolver instanceof PathStackResolver);

        $mimeResolver = new MimeResolver;
        $resolver->setMimeResolver($mimeResolver);

        $resolver->addPath(__DIR__);

        $fileAsset = new Asset\FileAsset(__FILE__);
        $fileAsset->mimetype = $mimeResolver->getMimeType(__FILE__);

        $this->assertEquals($fileAsset, $resolver->resolve(basename(__FILE__)));
        $this->assertNull($resolver->resolve('i-do-not-exist.php'));
    }

    public function testWillNotResolveDirectories(): void
    {
        $resolver = new PathStackResolver();
        $resolver->addPath(__DIR__ . '/..');

        $this->assertNull($resolver->resolve(basename(__DIR__)));
    }

    public function testLfiProtection(): void
    {
        $mimeResolver = new MimeResolver;
        $resolver = new PathStackResolver;
        $resolver->setMimeResolver($mimeResolver);

        // should be on by default
        $this->assertTrue($resolver->isLfiProtectionOn());
        $resolver->addPath(__DIR__);

        $this->assertNull($resolver->resolve(
            '..' . DIRECTORY_SEPARATOR . basename(__DIR__) . DIRECTORY_SEPARATOR . basename(__FILE__)
        ));

        $resolver->setLfiProtection(false);

        $this->assertEquals(
            file_get_contents(__FILE__),
            $resolver->resolve(
                '..' . DIRECTORY_SEPARATOR . basename(__DIR__) . DIRECTORY_SEPARATOR . basename(__FILE__)
            )->dump()
        );
    }

    public function testWillRefuseInvalidPath(): void
    {
        $resolver = new PathStackResolver();
        $this->expectException(InvalidArgumentException::class);
        $resolver->addPath(null);
    }

    /**
     * Test Collect returns valid list of assets
     *
     * @covers \AssetManager\Resolver\PathStackResolver::collect
     */
    public function testCollect(): void
    {
        $resolver = new PathStackResolver();
        $resolver->addPath(__DIR__);

        $this->assertContains(basename(__FILE__), $resolver->collect());
        $this->assertNotContains('i-do-not-exist.php', $resolver->collect());
    }

    /**
     * Test Collect returns valid list of assets
     *
     * @covers \AssetManager\Resolver\PathStackResolver::collect
     */
    public function testCollectDirectory(): void
    {
        $resolver = new PathStackResolver();
        $resolver->addPath(realpath(__DIR__ . '/../'));
        $dir = substr(__DIR__, strrpos(__DIR__, '/', 0) + 1);

        $this->assertContains($dir . DIRECTORY_SEPARATOR . basename(__FILE__), $resolver->collect());
        $this->assertNotContains($dir . DIRECTORY_SEPARATOR . 'i-do-not-exist.php', $resolver->collect());
    }
}
