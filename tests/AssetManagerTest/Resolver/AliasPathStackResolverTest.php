<?php

namespace AssetManagerTest\Service;

use Assetic\Asset;
use AssetManager\Resolver\AliasPathStackResolver;
use AssetManager\Service\MimeResolver;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for the Alias Path Stack Resolver
 */
class AliasPathStackResolverTest extends TestCase
{
    /**
     * Test constructor passes
     *
     * @covers \AssetManager\Resolver\AliasPathStackResolver::__construct
     */
    public function testConstructor(): void
    {
        $aliases = array(
            'alias1' => __DIR__ . DIRECTORY_SEPARATOR,
        );

        $resolver = new AliasPathStackResolver($aliases);

        $reflectionClass = new \ReflectionClass(\AssetManager\Resolver\AliasPathStackResolver::class);
        $property        = $reflectionClass->getProperty('aliases');
        $property->setAccessible(true);

        $this->assertEquals(
            $aliases,
            $property->getValue($resolver)
        );
    }

    /**
     * Test constructor fails when aliases passed in is not an array
     *
     * @covers \AssetManager\Resolver\AliasPathStackResolver::__construct
     */
    public function testConstructorFail(): void
    {
        $this->expectError();

        if (PHP_MAJOR_VERSION >= 7) {
            $this->expectException('\TypeError');
        }

        new AliasPathStackResolver('this_should_fail');
    }

    /**
     * Test add alias method.
     *
     * @covers \AssetManager\Resolver\AliasPathStackResolver::addAlias
     */
    public function testAddAlias(): void
    {
        $resolver        = new AliasPathStackResolver(array());
        $reflectionClass = new \ReflectionClass(\AssetManager\Resolver\AliasPathStackResolver::class);
        $addAlias        = $reflectionClass->getMethod('addAlias');

        $addAlias->setAccessible(true);

        $property = $reflectionClass->getProperty('aliases');

        $property->setAccessible(true);

        $addAlias->invoke($resolver, 'alias', 'path');

        $this->assertEquals(
            array('alias' => 'path' . DIRECTORY_SEPARATOR),
            $property->getValue($resolver)
        );
    }

    /**
     * Test addAlias fails with bad key
     *
     * @covers \AssetManager\Resolver\AliasPathStackResolver::addAlias
     */
    public function testAddAliasFailsWithBadKey(): void
    {
        $this->expectException(\AssetManager\Exception\InvalidArgumentException::class);

        $resolver        = new AliasPathStackResolver(array());
        $reflectionClass = new \ReflectionClass(\AssetManager\Resolver\AliasPathStackResolver::class);
        $addAlias        = $reflectionClass->getMethod('addAlias');

        $addAlias->setAccessible(true);

        $property = $reflectionClass->getProperty('aliases');
        $property->setAccessible(true);

        $addAlias->invoke($resolver, null, 'path');
    }

    /**
     * Test addAlias fails with bad Path
     *
     * @covers \AssetManager\Resolver\AliasPathStackResolver::addAlias
     */
    public function testAddAliasFailsWithBadPath(): void
    {
        $this->expectException(\AssetManager\Exception\InvalidArgumentException::class);

        $resolver = new AliasPathStackResolver(array());

        $reflectionClass = new \ReflectionClass(\AssetManager\Resolver\AliasPathStackResolver::class);

        $addAlias = $reflectionClass->getMethod('addAlias');
        $addAlias->setAccessible(true);

        $property = $reflectionClass->getProperty('aliases');
        $property->setAccessible(true);

        $addAlias->invoke($resolver, 'alias', null);
    }

    /**
     * Test normalize path
     *
     * @covers \AssetManager\Resolver\AliasPathStackResolver::normalizePath
     */
    public function testNormalizePath(): void
    {
        $resolver        = new AliasPathStackResolver(array());
        $reflectionClass = new \ReflectionClass(\AssetManager\Resolver\AliasPathStackResolver::class);
        $addAlias        = $reflectionClass->getMethod('normalizePath');

        $addAlias->setAccessible(true);

        $result = $addAlias->invoke($resolver, 'somePath\/\/\/');

        $this->assertEquals(
            'somePath' . DIRECTORY_SEPARATOR,
            $result
        );
    }

    /**
     * Test Set Mime Resolver Only Accepts a mime Resolver
     *
     * @covers \AssetManager\Resolver\AliasPathStackResolver::setMimeResolver
     * @covers \AssetManager\Resolver\AliasPathStackResolver::getMimeResolver
     */
    public function testGetAndSetMimeResolver(): void
    {
        $mimeReolver = $this->getMockBuilder(MimeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resolver = new AliasPathStackResolver(array('my/alias/' => __DIR__));

        $resolver->setMimeResolver($mimeReolver);

        $returned = $resolver->getMimeResolver();

        $this->assertEquals($mimeReolver, $returned);
    }

    /**
     * Test Set Mime Resolver Only Accepts a mime Resolver
     *
     * @covers \AssetManager\Resolver\AliasPathStackResolver::setMimeResolver
     */
    public function testSetMimeResolverFailObject(): void
    {
        $this->expectError();

        if (PHP_MAJOR_VERSION >= 7) {
            $this->expectException('\TypeError');
        }

        $resolver = new AliasPathStackResolver(array('my/alias/' => __DIR__));
        $resolver->setMimeResolver(new \stdClass());
    }

    /**
     * Test Lfi Protection Flag Defaults to true
     *
     * @covers \AssetManager\Resolver\AliasPathStackResolver::isLfiProtectionOn
     */
    public function testLfiProtectionFlagDefaultsTrue(): void
    {
        $resolver = new AliasPathStackResolver(array('my/alias/' => __DIR__));
        $returned = $resolver->isLfiProtectionOn();

        $this->assertTrue($returned);
    }

    /**
     * Test Get and Set of Lfi Protection Flag
     *
     * @covers \AssetManager\Resolver\AliasPathStackResolver::setLfiProtection
     * @covers \AssetManager\Resolver\AliasPathStackResolver::isLfiProtectionOn
     */
    public function testGetAndSetOfLfiProtectionFlag(): void
    {
        $resolver = new AliasPathStackResolver(array('my/alias/' => __DIR__));
        $resolver->setLfiProtection(true);
        $returned = $resolver->isLfiProtectionOn();

        $this->assertTrue($returned);

        $resolver->setLfiProtection(false);
        $returned = $resolver->isLfiProtectionOn();

        $this->assertFalse($returned);
    }

    /**
     * Test Resolve returns valid asset
     *
     * @covers \AssetManager\Resolver\AliasPathStackResolver::resolve
     */
    public function testResolve(): void
    {
        $resolver = new AliasPathStackResolver(array('my/alias/' => __DIR__));
        $this->assertTrue($resolver instanceof AliasPathStackResolver);
        $mimeResolver = new MimeResolver();
        $resolver->setMimeResolver($mimeResolver);
        $fileAsset           = new Asset\FileAsset(__FILE__);
        $fileAsset->mimetype = $mimeResolver->getMimeType(__FILE__);
        $this->assertEquals($fileAsset, $resolver->resolve('my/alias/' . basename(__FILE__)));
        $this->assertNull($resolver->resolve('i-do-not-exist.php'));
    }

    /**
     * Test Resolve returns valid asset
     *
     * @covers \AssetManager\Resolver\AliasPathStackResolver::resolve
     */
    public function testResolveWhenAliasStringDoesnotContainTrailingSlash(): void
    {
        $resolver = new AliasPathStackResolver(array('my/alias' => __DIR__));
        $mimeResolver = new MimeResolver();
        $resolver->setMimeResolver($mimeResolver);
        $fileAsset           = new Asset\FileAsset(__FILE__);
        $fileAsset->mimetype = $mimeResolver->getMimeType(__FILE__);
        $this->assertEquals($fileAsset, $resolver->resolve('my/alias/' . basename(__FILE__)));
    }

    /**
     * @covers \AssetManager\Resolver\AliasPathStackResolver::resolve
     */
    public function testResolveWhenAliasExistsInPath(): void
    {
        $resolver     = new AliasPathStackResolver(array('AliasPathStackResolverTest/' => __DIR__));
        $mimeResolver = new MimeResolver();
        $resolver->setMimeResolver($mimeResolver);
        $fileAsset           = new Asset\FileAsset(__FILE__);
        $fileAsset->mimetype = $mimeResolver->getMimeType(__FILE__);
        $this->assertEquals($fileAsset, $resolver->resolve('AliasPathStackResolverTest/' . basename(__FILE__)));
        
        $map = array(
            'AliasPathStackResolverTest/' => __DIR__,
            'prefix/AliasPathStackResolverTest/' =>  __DIR__
        );
        $resolver = new AliasPathStackResolver($map);
        $resolver->setMimeResolver(new MimeResolver());
        $fileAsset           = new Asset\FileAsset(__FILE__);
        $fileAsset->mimetype = $mimeResolver->getMimeType(__FILE__);
        $this->assertEquals($fileAsset, $resolver->resolve('prefix/AliasPathStackResolverTest/' . basename(__FILE__)));
    }

    /**
     * Test that resolver will not resolve directories
     *
     * @covers \AssetManager\Resolver\AliasPathStackResolver::resolve
     */
    public function testWillNotResolveDirectories(): void
    {
        $resolver = new AliasPathStackResolver(array('my/alias/' => __DIR__ . '/..'));
        $this->assertNull($resolver->resolve('my/alias/' . basename(__DIR__)));
    }

    /**
     * Test Lfi Protection
     *
     * @covers \AssetManager\Resolver\AliasPathStackResolver::resolve
     */
    public function testLfiProtection(): void
    {
        $mimeResolver = new MimeResolver();
        $resolver     = new AliasPathStackResolver(array('my/alias/' => __DIR__));
        $resolver->setMimeResolver($mimeResolver);

        // should be on by default
        $this->assertTrue($resolver->isLfiProtectionOn());

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

    /**
     * Test Collect returns valid list of assets
     *
     * @covers \AssetManager\Resolver\AliasPathStackResolver::collect
     */
    public function testCollect(): void
    {
        $alias    = 'my/alias/';
        $resolver = new AliasPathStackResolver(array($alias => __DIR__));

        $this->assertContains($alias . basename(__FILE__), $resolver->collect());
        $this->assertNotContains($alias . 'i-do-not-exist.php', $resolver->collect());
    }

    /**
     * Test Collect returns valid list of assets
     *
     * @covers \AssetManager\Resolver\AliasPathStackResolver::collect
     */
    public function testCollectDirectory(): void
    {
        $alias    = 'my/alias/';
        $resolver = new AliasPathStackResolver(array($alias => realpath(__DIR__ . '/../')));
        $dir      = substr(__DIR__, strrpos(__DIR__, DIRECTORY_SEPARATOR, 0) + 1);

        $this->assertContains($alias . $dir . DIRECTORY_SEPARATOR . basename(__FILE__), $resolver->collect());
        $this->assertNotContains($alias . $dir . DIRECTORY_SEPARATOR . 'i-do-not-exist.php', $resolver->collect());
    }
}
