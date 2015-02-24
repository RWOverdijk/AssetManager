<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use AssetManager\Resolver\AggregateResolver;
use AssetManager\Resolver\ResolverInterface;

class AggregateResolverTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        require_once __DIR__ . '/../../_files/ResolverWithCollect.php';
    }

    public function testResolve()
    {
        $resolver = new AggregateResolver();

        $this->assertTrue($resolver instanceof ResolverInterface);

        $lowPriority = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $lowPriority
            ->expects($this->once())
            ->method('resolve')
            ->with('to-be-resolved')
            ->will($this->returnValue('first'));
        $resolver->attach($lowPriority);

        $this->assertSame('first', $resolver->resolve('to-be-resolved'));

        $highPriority = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $highPriority
            ->expects($this->exactly(2))
            ->method('resolve')
            ->with('to-be-resolved')
            ->will($this->returnValue('second'));
        $resolver->attach($highPriority, 1000);

        $this->assertSame('second', $resolver->resolve('to-be-resolved'));

        $averagePriority = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $averagePriority
            ->expects($this->never())
            ->method('resolve')
            ->will($this->returnValue('third'));
        $resolver->attach($averagePriority, 500);

        $this->assertSame('second', $resolver->resolve('to-be-resolved'));
    }

    public function testCollect()
    {
        /* Tests for interfaces that _do_ implement the `collect` method. */
        $resolver    = new AggregateResolver();
        $lowPriority = $this->getMock('ResolverWithCollect');
        $lowPriority
            ->expects($this->exactly(2))
            ->method('collect')
            ->will($this->returnValue(array('one', 'two')));
        $resolver->attach($lowPriority);

        $this->assertContains('one', $resolver->collect());

        $highPriority = $this->getMock('ResolverWithCollect');
        $highPriority
            ->expects($this->once())
            ->method('collect')
            ->will($this->returnValue(array('three')));
        $resolver->attach($highPriority, 1000);

        $collection = $resolver->collect();
        $this->assertContains('one', $collection);
        $this->assertContains('three', $collection);

        $this->assertCount(3, $collection);

        /* Tests for interfaces that _don't_ implement the `collect` method. */
        $resolver    = new AggregateResolver();
        $lowPriority = $this->getMock('AssetManager\Resolver\ResolverInterface');

        $resolver->attach($lowPriority);

        $this->assertEquals(array(), $resolver->collect());

        $highPriority = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $resolver->attach($highPriority, 1000);

        $collection = $resolver->collect();
        $this->assertEquals(array(), $collection);

        $this->assertCount(0, $collection);
    }
}
