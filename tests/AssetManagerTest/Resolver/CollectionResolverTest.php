<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use ArrayObject;
use AssetManager\Resolver\CollectionResolver;

class CollectionsResolverTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $resolver = new CollectionResolver();
        $this->assertSame(array(), $resolver->getCollections());
        $resolver = new CollectionResolver(array(
            'key1' => 'value1',
            'key2' => 'value2',
        ));
        $this->assertSame(
            array(
                'key1' => 'value1',
                'key2' => 'value2',
            ),
            $resolver->getCollections()
        );
    }

    public function testCollections()
    {
        $resolver = new CollectionResolver(array(
            'key1' => 'value1',
            'key2' => 'value2',
        ));

        $this->assertSame('value1', $resolver->resolve('key1'));
        $this->assertSame('value2', $resolver->resolve('key2'));
        $this->assertNull($resolver->resolve('key3'));
    }

    public function testSetCollections()
    {
        $resolver = new CollectionResolver();
        $resolver->setCollections(array(
            'key1' => 'value1',
            'key2' => 'value2',
        ));

        $this->assertSame(
            array(
                'key1' => 'value1',
                'key2' => 'value2',
            ),
            $resolver->getCollections()
        );

        $resolver->setCollections(new ArrayObject(array(
            'key3' => 'value3',
            'key4' => 'value4',
        )));

        $this->assertSame(
            array(
                'key3' => 'value3',
                'key4' => 'value4',
            ),
            $resolver->getCollections()
        );

    }

    public function testWillRefuseInvalidCollections()
    {
        $resolver = new CollectionResolver();
        $this->setExpectedException('AssetManager\Exception\InvalidArgumentException');
        $resolver->setCollections('invalid');
    }
}
