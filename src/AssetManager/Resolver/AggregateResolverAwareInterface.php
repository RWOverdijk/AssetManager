<?php

namespace AssetManager\Resolver;

use AssetManager\Resolver\ResolverInterface;

interface AggregateResolverAwareInterface
{
    /**
     * Set the aggregate resolver.
     *
     * @param ResolverInterface $aggregateResolver
     */
    public function setAggregateResolver(ResolverInterface $aggregateResolver);

    /**
     * Get the aggregate resolver.
     *
     * @return ResolverInterface
     */
    public function getAggregateResolver();
}
