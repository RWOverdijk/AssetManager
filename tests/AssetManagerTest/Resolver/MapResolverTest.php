<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use ArrayObject;
use AssetManager\Resolver\MapResolver;

class MapResolverTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $resolver = new MapResolver();
        $this->assertSame(array(), $resolver->getMap());
        $resolver = new MapResolver(array(
            'key1' => 'value1',
            'key2' => 'value2',
        ));
        $this->assertSame(
            array(
                'key1' => 'value1',
                'key2' => 'value2',
            ),
            $resolver->getMap()
        );
    }

    public function testMap()
    {
        $resolver = new MapResolver(array(
            'key1' => 'value1',
            'key2' => 'value2',
        ));

        $this->assertSame('value1', $resolver->resolve('key1'));
        $this->assertSame('value2', $resolver->resolve('key2'));
        $this->assertNull($resolver->resolve('key3'));
    }

    public function testSetMap()
    {
        $resolver = new MapResolver();
        $resolver->setMap(array(
            'key1' => 'value1',
            'key2' => 'value2',
        ));

        $this->assertSame(
            array(
                'key1' => 'value1',
                'key2' => 'value2',
            ),
            $resolver->getMap()
        );

        $resolver->setMap(new ArrayObject(array(
            'key3' => 'value3',
            'key4' => 'value4',
        )));

        $this->assertSame(
            array(
                'key3' => 'value3',
                'key4' => 'value4',
            ),
            $resolver->getMap()
        );

    }

    public function testWillRefuseInvalidMap()
    {
        $resolver = new MapResolver();
        $this->setExpectedException('AssetManager\Exception\InvalidArgumentException');
        $resolver->setMap('invalid');
    }
}
