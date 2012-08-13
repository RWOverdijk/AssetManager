<?php

namespace AssetManager\Resolver;

use Zend\Stdlib\PriorityQueue;

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

        return $this;
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
}
