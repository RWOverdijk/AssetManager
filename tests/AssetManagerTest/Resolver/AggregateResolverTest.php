<?php

namespace AssetManagerTest\Service;

use AssetManager\Resolver\AggregateResolver;
use AssetManager\Resolver\ResolverInterface;
use PHPUnit\Framework\TestCase;

class AggregateResolverTest extends TestCase
{
    public function testResolve(): void
    {
        $resolver = new AggregateResolver();

        $this->assertTrue($resolver instanceof ResolverInterface);

        $lowPriority = $this->createMock(ResolverInterface::class);
        $lowPriority
            ->expects($this->once())
            ->method('resolve')
            ->with('to-be-resolved')
            ->will($this->returnValue('first'));
        $resolver->attach($lowPriority);

        $this->assertSame('first', $resolver->resolve('to-be-resolved'));

        $highPriority = $this->createMock(ResolverInterface::class);
        $highPriority
            ->expects($this->exactly(2))
            ->method('resolve')
            ->with('to-be-resolved')
            ->will($this->returnValue('second'));
        $resolver->attach($highPriority, 1000);

        $this->assertSame('second', $resolver->resolve('to-be-resolved'));

        $averagePriority = $this->createMock(ResolverInterface::class);
        $averagePriority
            ->expects($this->never())
            ->method('resolve')
            ->will($this->returnValue('third'));
        $resolver->attach($averagePriority, 500);

        $this->assertSame('second', $resolver->resolve('to-be-resolved'));
    }

    public function testCollectWithCollectMethod(): void
    {
        $resolver    = new AggregateResolver();
        $lowPriority = $this->getMockBuilder(ResolverInterface::class)
            ->setMethods(array('resolve', 'collect'))
            ->getMock();

        $lowPriority
            ->expects($this->exactly(2))
            ->method('collect')
            ->will($this->returnValue(array('one', 'two')));
        $resolver->attach($lowPriority);

        $this->assertContains('one', $resolver->collect());

        $highPriority = $this->getMockBuilder(ResolverInterface::class)
            ->setMethods(array('resolve', 'collect'))
            ->getMock();

        $highPriority
            ->expects($this->once())
            ->method('collect')
            ->will($this->returnValue(array('three')));
        $resolver->attach($highPriority, 1000);

        $collection = $resolver->collect();
        $this->assertContains('one', $collection);
        $this->assertContains('three', $collection);

        $this->assertCount(3, $collection);
    }

    public function testCollectWithoutCollectMethod(): void
    {
        $resolver    = new AggregateResolver();
        $lowPriority = $this->createMock(ResolverInterface::class);

        $resolver->attach($lowPriority);

        $this->assertEquals(array(), $resolver->collect());

        $highPriority = $this->createMock(ResolverInterface::class);
        $resolver->attach($highPriority, 1000);

        $collection = $resolver->collect();
        $this->assertEquals(array(), $collection);

        $this->assertCount(0, $collection);
    }
}
