<?php

namespace AssetManagerTest\Service;

use AssetManager\Resolver\GlobPathStackResolver;
use PHPUnit_Framework_TestCase;
use ArrayObject;
use Assetic\Asset;
use AssetManager\Resolver\ResolverInterface;
use AssetManager\Resolver\MimeResolverAwareInterface;
use AssetManager\Service\MimeResolver;

class GlobPathStackResolverTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $resolver = new GlobPathStackResolver();
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

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testSetMimeResolverFailObject()
    {
        $resolver = new GlobPathStackResolver();
        $resolver->setMimeResolver(new \stdClass());
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testSetMimeResolverFailString()
    {
        $resolver = new GlobPathStackResolver();
        $resolver->setMimeResolver('invalid');
    }

    public function testSetPaths()
    {
        $resolver = new GlobPathStackResolver();
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
        $resolver = new GlobPathStackResolver();
        $this->assertTrue($resolver instanceof GlobPathStackResolver);

        $mimeResolver = new MimeResolver;
        $resolver->setMimeResolver($mimeResolver);

        $resolver->addPath(__DIR__);

        $fileAsset = new Asset\GlobAsset(__DIR__ . DIRECTORY_SEPARATOR . '*');
        $fileAsset->mimetype = $mimeResolver->getMimeType(__FILE__);

        $this->assertEquals($fileAsset, $resolver->resolve('*'));

        $this->assertNull($resolver->resolve('i-do-not-exist.php'));
    }

    public function testWillNotResolveDirectories()
    {
        $resolver = new GlobPathStackResolver();
        $resolver->addPath(__DIR__ . DIRECTORY_SEPARATOR . '..');

        $this->assertNull($resolver->resolve(basename(__DIR__)));
    }

    public function testLfiProtection()
    {
        $mimeResolver = new MimeResolver;
        $resolver = new GlobPathStackResolver;
        $resolver->setMimeResolver($mimeResolver);

        // should be on by default
        $this->assertTrue($resolver->isLfiProtectionOn());
        $resolver->addPath(__DIR__);

        $this->assertNull($resolver->resolve(
            '..' . DIRECTORY_SEPARATOR . basename(__DIR__) . DIRECTORY_SEPARATOR . '*'
        ));

        $resolver->setLfiProtection(false);

        $this->assertEquals(
            file_get_contents(__FILE__),
            $resolver->resolve(
                '..' . DIRECTORY_SEPARATOR . basename(__DIR__) . DIRECTORY_SEPARATOR . 'GlobPathStackResolverTest.*'
            )->dump()
        );
    }

    public function testWillRefuseInvalidPath()
    {
        $resolver = new GlobPathStackResolver();
        $this->setExpectedException('AssetManager\Exception\InvalidArgumentException');
        $resolver->addPath(null);
    }

}
