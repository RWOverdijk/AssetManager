<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use Assetic\Asset;
use AssetManager\Resolver\ConcatResolver;
use AssetManager\Resolver\AggregateResolverAwareInterface;
use AssetManager\Resolver\ResolverInterface;
use AssetManager\Service\MimeResolver;

class ConcatResolverTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $resolver = new ConcatResolver(
            array(
                 'key1' => array(
                     __FILE__
                 ),
                 'key2' => array(
                     __FILE__
                 ),
            )
        );

        $this->assertTrue($resolver instanceof ResolverInterface);
        $this->assertTrue($resolver instanceof AggregateResolverAwareInterface);

        $this->assertSame(
            array(
                 'key1' => array(
                     __FILE__
                 ),
                 'key2' => array(
                     __FILE__
                 ),
            ),
            $resolver->getConcats()
        );
    }

    public function testSetGetAggregateResolver()
    {
        $resolver = new ConcatResolver;

        $aggregateResolver = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $aggregateResolver
            ->expects($this->once())
            ->method('resolve')
            ->with('say')
            ->will($this->returnValue('world'));

        $resolver->setAggregateResolver($aggregateResolver);

        $this->assertEquals('world', $resolver->getAggregateResolver()->resolve('say'));
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testSetAggregateResolverFails()
    {
        $resolver = new ConcatResolver;

        $resolver->setAggregateResolver(new \stdClass);
    }

    public function testSetConcatSuccess()
    {
        $resolver = new ConcatResolver;

        $resolver->setConcats(new ConcatIterable);

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
            $resolver->getConcats()
        );
    }

    /**
     * @expectedException \Zend\Stdlib\Exception\InvalidArgumentException
     */
    public function testSetConcatFails()
    {
        $resolver = new ConcatResolver;
        $resolver->setConcats(new \stdClass);
    }

    public function testGetConcat()
    {
        $resolver = new ConcatResolver;
        $this->assertSame(array(), $resolver->getConcats());
    }

    public function testResolveNull()
    {
        $resolver = new ConcatResolver;
        $this->assertNull($resolver->resolve('bacon'));
    }

    public function testResolveAssetFail()
    {
        $resolver = new ConcatResolver;

        $asset1 = array(
            'bacon' => 'yummy',
        );

        $this->assertNull($resolver->setConcats($asset1));
    }

    public function testResolveAssetSuccess()
    {
        $resolver = new ConcatResolver;

        $asset1 = array(
            'bacon' => array(
                __FILE__,
                __FILE__,
            ),
        );

        $callback = function ($file) {
            $asset = new \Assetic\Asset\FileAsset(
                $file
            );

            return $asset;
        };

        $aggregateResolver = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $aggregateResolver
            ->expects($this->exactly(2))
            ->method('resolve')
            ->will($this->returnCallback($callback));
        $resolver->setAggregateResolver($aggregateResolver);

        $assetFilterManager = new \AssetManager\Service\AssetFilterManager();
        $mimeResolver = new MimeResolver;
        $assetFilterManager->setMimeResolver($mimeResolver);
        $resolver->setMimeResolver($mimeResolver);
        $resolver->setAssetFilterManager($assetFilterManager);

        $resolver->setConcats($asset1);

        $asset      = $resolver->resolve('bacon');

        $this->assertTrue($asset instanceof \AssetManager\Asset\AggregateAsset);
        $this->assertEquals(
            $asset->dump(),
            file_get_contents(__FILE__).file_get_contents(__FILE__)
        );
    }

    /**
     * Test Collect returns valid list of assets
     *
     * @covers \AssetManager\Resolver\ConcatResolver::collect
     */
    public function testCollect()
    {
        $concats = array(
            'myCollection' => array(
                'bacon',
                'eggs',
                'mud',
            ),
            'my/collect.ion' => array(
                'bacon',
                'eggs',
                'mud',
            ),
        );
        $resolver = new ConcatResolver($concats);

        $this->assertEquals(array_keys($concats), $resolver->collect());
    }
}
