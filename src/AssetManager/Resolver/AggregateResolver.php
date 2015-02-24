<?php

namespace AssetManager\Resolver;

use Zend\Stdlib\PriorityQueue;

/**
 * The aggregate resolver consists out of a multitude of
 * resolvers defined by the ResolverInterface.
 */
class AggregateResolver implements ResolverInterface
{
    /**
     * @var PriorityQueue|ResolverInterface[]
     */
    protected $queue;

    /**
     * Constructor
     *
     * Instantiate the internal priority queue
     */
    public function __construct()
    {
        $this->queue = new PriorityQueue();
    }

    /**
     * Attach a resolver
     *
     * @param  ResolverInterface $resolver
     * @param  int               $priority
     * @return self
     */
    public function attach(ResolverInterface $resolver, $priority = 1)
    {
        $this->queue->insert($resolver, $priority);
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($name)
    {
        foreach ($this->queue as $resolver) {
            $resource = $resolver->resolve($name);
            if (null !== $resource) {
                return $resource;
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function collect()
    {
        $collection = array();

        foreach ($this->queue as $resolver) {
            if (!method_exists($resolver, 'collect')) {
                continue;
            }

            $collection = array_merge($resolver->collect(), $collection);
        }

        return array_unique($collection);
    }
}
