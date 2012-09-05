<?php

use AssetManager\Resolver;

class InterfaceTestResolver implements
    Resolver\ResolverInterface,
    Resolver\AggregateResolverAwareInterface,
    Resolver\MimeResolverAwareInterface
{
    public $calledMime;
    public $calledAggregate;

    public function resolve($path)
    {
    }

    public function setAggregateResolver()
    {
        $this->calledAggregate = true;
    }

    public function setMimeResolver()
    {
        $this->calledMime = true;
    }
}