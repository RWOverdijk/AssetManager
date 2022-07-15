<?php

namespace AssetManagerTest\Service;

use Assetic\Asset;
use AssetManager\Resolver\MapResolver;
use AssetManager\Resolver\MimeResolverAwareInterface;
use AssetManager\Service\MimeResolver;
use PHPUnit\Framework\TestCase;

class MapResolverTest extends TestCase
{
    public function testConstruct(): void
    {
        $resolver = new MapResolver(
            array(
                'key1' => 'value1',
                'key2' => 'value2'
            )
        );

        $this->assertSame(
            array(
                'key1' => 'value1',
                'key2' => 'value2'
            ),
            $resolver->getMap()
        );
    }

    public function testGetMimeResolver(): void
    {
        $resolver = new MapResolver;
        $this->assertNull($resolver->getMimeResolver());
    }

    public function testSetMapSuccess(): void
    {
        $resolver = new MapResolver;
        $resolver->setMap(new MapIterable);

        $this->assertEquals(
            array(
                'mapName1' => array(
                    'map 1.1',
                    'map 1.2',
                    'map 1.3',
                    'map 1.4',
                ),
                'mapName2' => array(
                    'map 2.1',
                    'map 2.2',
                    'map 2.3',
                    'map 2.4',
                ),
                'mapName3' => array(
                    'map 3.1',
                    'map 3.2',
                    'map 3.3',
                    'map 3.4',
                )
            ),
            $resolver->getMap()
        );
    }

    public function testSetMapFails(): void
    {
        $this->expectException(\AssetManager\Exception\InvalidArgumentException::class);
        $resolver = new MapResolver;
        $resolver->setMap(new \stdClass);
    }

    public function testGetMap(): void
    {
        $resolver = new MapResolver;
        $this->assertSame(array(), $resolver->getMap());
    }

    public function testResolveNull(): void
    {
        $resolver = new MapResolver;
        $this->assertNull($resolver->resolve('bacon'));
    }

    public function testResolveAssetFail(): void
    {
        $resolver = new MapResolver;

        $asset1 = array(
            'bacon' => 'porn',
        );

        $this->assertNull($resolver->setMap($asset1));
    }

    public function testResolveAssetSuccess(): void
    {
        $resolver = new MapResolver;

        $this->assertTrue($resolver instanceof MimeResolverAwareInterface);

        $mimeResolver = new MimeResolver;

        $resolver->setMimeResolver($mimeResolver);

        $asset1 = array(
            'bacon.php' => __FILE__,
        );

        $resolver->setMap($asset1);

        $asset      = $resolver->resolve('bacon.php');
        $mimetype   = $mimeResolver->getMimeType(__FILE__);

        $this->assertTrue($asset instanceof Asset\FileAsset);
        $this->assertEquals($mimetype, $asset->mimetype);
        $this->assertEquals($asset->dump(), file_get_contents(__FILE__));
    }

    public function testResolveHttpAssetSuccess(): void
    {
        $resolver     = new MapResolver;
        $mimeResolver = $this->createMock(MimeResolver::class);

        $mimeResolver->expects($this->any())
                ->method('getMimeType')
                ->with('bacon.bar')
                ->will($this->returnValue('text/foo'));

        $resolver->setMimeResolver($mimeResolver);

        $asset1 = array(
            'bacon.bar' => 'http://foo.bar/',
        );

        $resolver->setMap($asset1);

        $asset      = $resolver->resolve('bacon.bar');

        $this->assertTrue($asset instanceof Asset\HttpAsset);
        $this->assertSame('text/foo', $asset->mimetype);
    }

    /**
     * Test Collect returns valid list of assets
     *
     * @covers \AssetManager\Resolver\MapResolver::collect
     */
    public function testCollect(): void
    {
        $map = array(
            'foo' => 'bar',
            'baz' => 'qux',
        );
        $resolver = new MapResolver($map);

        $this->assertEquals(array_keys($map), $resolver->collect());
    }
}
