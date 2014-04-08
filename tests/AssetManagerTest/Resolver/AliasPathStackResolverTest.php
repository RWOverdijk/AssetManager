<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use Assetic\Asset;
use AssetManager\Resolver\AliasPathStackResolver;
use AssetManager\Resolver\ResolverInterface;
use AssetManager\Resolver\MimeResolverAwareInterface;
use AssetManager\Service\MimeResolver;

class AliasPathStackResolverTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $resolver = new AliasPathStackResolver();
        $this->assertEmpty($resolver->getAliases());

        $resolver->addAliases(array('alias1' => __DIR__));
        $this->assertEquals(array('alias1' => __DIR__ . DIRECTORY_SEPARATOR), $resolver->getAliases());

        $resolver->clearAliases();
        $this->assertEquals(array(), $resolver->getAliases());

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
        $resolver = new AliasPathStackResolver();
        $resolver->setMimeResolver(new \stdClass());
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testSetMimeResolverFailString()
    {
        $resolver = new AliasPathStackResolver();
        $resolver->setMimeResolver('invalid');
    }

    public function testSetPaths()
    {
        $resolver = new AliasPathStackResolver();

        $aliases = array(
            'alias1' => 'some/dir',
            'alias2' => 'some/other/dir'
        );

        $resolver->setAliases($aliases);

        $expected = array(
            'alias1' => 'some/dir'. DIRECTORY_SEPARATOR,
            'alias2' => 'some/other/dir'. DIRECTORY_SEPARATOR
        );

        // order inverted because of how a stack is traversed
        $this->assertSame(
            $expected,
            $resolver->getAliases()
        );

        $aliases = array(
            'alias3' => 'dir/three',
            'alias4' => 'dir/four'
        );

        $resolver->setAliases($aliases);

        $expected = array(
            'alias3' => 'dir/three'. DIRECTORY_SEPARATOR,
            'alias4' => 'dir/four'. DIRECTORY_SEPARATOR
        );

        $this->assertSame(
            $expected,
            $resolver->getAliases()
        );

        $this->setExpectedException('AssetManager\Exception\InvalidArgumentException');
        $resolver->setAliases('invalid');

    }

    public function testResolve()
    {
        $resolver = new AliasPathStackResolver();
        $this->assertTrue($resolver instanceof AliasPathStackResolver);

        $mimeResolver = new MimeResolver;
        $resolver->setMimeResolver($mimeResolver);

        $resolver->addAlias('my/alias/', __DIR__);

        $fileAsset = new Asset\FileAsset(__FILE__);
        $fileAsset->mimetype = $mimeResolver->getMimeType(__FILE__);

        $this->assertEquals($fileAsset, $resolver->resolve('my/alias/'.basename(__FILE__)));
        $this->assertNull($resolver->resolve('i-do-not-exist.php'));
    }

    public function testWillNotResolveDirectories()
    {
        $resolver = new AliasPathStackResolver();
        $resolver->addAlias('my/alias/', __DIR__ . '/..');

        $this->assertNull($resolver->resolve('my/alias/'.basename(__DIR__)));
    }

    public function testLfiProtection()
    {
        $mimeResolver = new MimeResolver;
        $resolver = new AliasPathStackResolver;
        $resolver->setMimeResolver($mimeResolver);

        // should be on by default
        $this->assertTrue($resolver->isLfiProtectionOn());
        $resolver->addAlias('my/alias/', __DIR__);

        $this->assertNull($resolver->resolve(
            '..' . DIRECTORY_SEPARATOR . basename(__DIR__) . DIRECTORY_SEPARATOR . basename(__FILE__)
        ));

        $resolver->setLfiProtection(false);

        $this->assertEquals(
            file_get_contents(__FILE__),
            $resolver->resolve(
                'my/alias/..' . DIRECTORY_SEPARATOR . basename(__DIR__) . DIRECTORY_SEPARATOR . basename(__FILE__)
            )->dump()
        );
    }

    public function testWillRefuseInvalidPath()
    {
        $resolver = new AliasPathStackResolver();
        $this->setExpectedException('AssetManager\Exception\InvalidArgumentException');
        $resolver->addAlias(null, __DIR__);
    }
}
