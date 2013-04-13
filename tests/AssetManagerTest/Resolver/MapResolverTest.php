<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use ArrayObject;
use Assetic\Asset;
use AssetManager\Resolver\MapResolver;
use AssetManager\Resolver\MimeResolverAwareInterface;
use AssetManager\Service\MimeResolver;
use Zend\ServiceManager\ServiceLocatorInterface;

class MapIterable implements \IteratorAggregate
{
    public $mapName1 = array(
        'map 1.1',
        'map 1.2',
        'map 1.3',
        'map 1.4',
    );

    public $mapName2 = array(
        'map 2.1',
        'map 2.2',
        'map 2.3',
        'map 2.4',
    );

    public $mapName3 = array(
        'map 3.1',
        'map 3.2',
        'map 3.3',
        'map 3.4',
    );

    public function getIterator()
    {
        return new \ArrayIterator($this);
    }
}

class MapResolverTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
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

    public function testGetMimeResolver()
    {
        $resolver = new MapResolver;
        $this->assertNull($resolver->getMimeResolver());
    }

    public function testSetMapSuccess()
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
            ), $resolver->getMap()
         );
    }

    /**
     * @expectedException AssetManager\Exception\InvalidArgumentException
     */
    public function testSetMapFails()
    {
        $resolver = new MapResolver;
        $resolver->setMap(new \stdClass);
    }

    public function testGetMap()
    {
        $resolver = new MapResolver;
        $this->assertSame(array(), $resolver->getMap());
    }

    public function testResolveNull()
    {
        $resolver = new MapResolver;
        $this->assertNull($resolver->resolve('bacon'));
    }

    public function testResolveAssetFail()
    {
        $resolver = new MapResolver;

        $asset1 = array(
            'bacon' => 'porn',
        );

        $this->assertNull($resolver->setMap($asset1));
    }

    public function testResolveAssetSuccess()
    {
        $resolver = new MapResolver;

        $this->assertTrue($resolver instanceof MimeResolverAwareInterface);

        $mimeResolver = new MimeResolver;

        $resolver->setMimeResolver($mimeResolver);

        $asset1 = array(
            'bacon' => __FILE__,
        );

        $resolver->setMap($asset1);

        $asset      = $resolver->resolve('bacon');
        $mimetype   = $mimeResolver->getMimeType(__FILE__);

        $this->assertTrue($asset instanceof Asset\FileAsset);
        $this->assertEquals($mimetype, $asset->mimetype);
        $this->assertEquals($asset->dump(), file_get_contents(__FILE__));
    }

    public function testResolveHttpAssetSuccess()
    {
        $resolver     = new MapResolver;
        $mimeResolver = $this->getMock('AssetManager\Service\MimeResolver');

        $mimeResolver->expects($this->any())
                ->method('getMimeType')
                ->with('http://foo.bar/')
                ->will($this->returnValue('text/foo'));

        $resolver->setMimeResolver($mimeResolver);

        $asset1 = array(
            'bacon' => 'http://foo.bar/',
        );

        $resolver->setMap($asset1);

        $asset      = $resolver->resolve('bacon');

        $this->assertTrue($asset instanceof Asset\HttpAsset);
        $this->assertSame('text/foo', $asset->mimetype);
    }
}
