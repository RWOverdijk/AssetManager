<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use Assetic\Asset;
use AssetManager\Resolver\AliasPathStackResolver;
use AssetManager\Service\MimeResolver;

class AliasPathStackResolverTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test constructor passes
     */
    public function testConstructor()
    {
        $aliases = array(
            'alias1' => __DIR__ . DIRECTORY_SEPARATOR,
        );

        $resolver = new AliasPathStackResolver($aliases);

        $reflectionClass = new \ReflectionClass('AssetManager\Resolver\AliasPathStackResolver');
        $property = $reflectionClass->getProperty('aliases');
        $property->setAccessible(true);

        $this->assertEquals(
            $aliases,
            $property->getValue($resolver)
        );

    }

    /**
     * Test constructor fails when aliases passed in is not an array
     *
     * @expectedException \AssetManager\Exception\InvalidArgumentException
     */
    public function testConstructorFail()
    {
        new AliasPathStackResolver('this_should_fail');
    }

    /**
     * Test add alias method.
     */
    public function testAddAlias()
    {
        $resolver = new AliasPathStackResolver(array());

        $reflectionClass = new \ReflectionClass('AssetManager\Resolver\AliasPathStackResolver');

        $addAlias = $reflectionClass->getMethod('addAlias');
        $addAlias->setAccessible(true);

        $property = $reflectionClass->getProperty('aliases');
        $property->setAccessible(true);

        $addAlias->invoke($resolver, 'alias', 'path');

        $this->assertEquals(
            array('alias' => 'path'.DIRECTORY_SEPARATOR),
            $property->getValue($resolver)
        );
    }

    /**
     * Test addAlias fails with bad key
     *
     * @expectedException \AssetManager\Exception\InvalidArgumentException
     */
    public function testAddAliasFailsWithBadKey()
    {
        $resolver = new AliasPathStackResolver(array());

        $reflectionClass = new \ReflectionClass('AssetManager\Resolver\AliasPathStackResolver');

        $addAlias = $reflectionClass->getMethod('addAlias');
        $addAlias->setAccessible(true);

        $property = $reflectionClass->getProperty('aliases');
        $property->setAccessible(true);

        $addAlias->invoke($resolver, null, 'path');
    }

    /**
     * Test addAlias fails with bad Path
     * @expectedException \AssetManager\Exception\InvalidArgumentException
     */
    public function testAddAliasFailsWithBadPath()
    {
        $resolver = new AliasPathStackResolver(array());

        $reflectionClass = new \ReflectionClass('AssetManager\Resolver\AliasPathStackResolver');

        $addAlias = $reflectionClass->getMethod('addAlias');
        $addAlias->setAccessible(true);

        $property = $reflectionClass->getProperty('aliases');
        $property->setAccessible(true);

        $addAlias->invoke($resolver, 'alias', null);
    }

    /**
     * Test normalize path
     */
    public function testNormalizePath()
    {
        $resolver = new AliasPathStackResolver(array());

        $reflectionClass = new \ReflectionClass('AssetManager\Resolver\AliasPathStackResolver');

        $addAlias = $reflectionClass->getMethod('normalizePath');
        $addAlias->setAccessible(true);

        $result = $addAlias->invoke($resolver, 'somePath\/\/\/');

        $this->assertEquals(
            'somePath'.DIRECTORY_SEPARATOR,
            $result
        );
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testSetMimeResolverFailObject()
    {
        $resolver = new AliasPathStackResolver(array('my/alias/' => __DIR__));
        $resolver->setMimeResolver(new \stdClass());
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testSetMimeResolverFailString()
    {
        $resolver = new AliasPathStackResolver(array('my/alias/' => __DIR__));
        $resolver->setMimeResolver('invalid');
    }

    /**
     * Test Resolve returns valid asset
     */
    public function testResolve()
    {
        $resolver = new AliasPathStackResolver(array('my/alias/' => __DIR__));
        $this->assertTrue($resolver instanceof AliasPathStackResolver);

        $mimeResolver = new MimeResolver;
        $resolver->setMimeResolver($mimeResolver);

        $fileAsset = new Asset\FileAsset(__FILE__);
        $fileAsset->mimetype = $mimeResolver->getMimeType(__FILE__);

        $this->assertEquals($fileAsset, $resolver->resolve('my/alias/'.basename(__FILE__)));
        $this->assertNull($resolver->resolve('i-do-not-exist.php'));
    }

    /**
     * Test that resolver will not resolve directories
     */
    public function testWillNotResolveDirectories()
    {
        $resolver = new AliasPathStackResolver(array('my/alias/' => __DIR__ . '/..'));
        $this->assertNull($resolver->resolve('my/alias/'.basename(__DIR__)));
    }

    public function testLfiProtection()
    {
        $mimeResolver = new MimeResolver;
        $resolver = new AliasPathStackResolver(array('my/alias/' => __DIR__));
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
}
