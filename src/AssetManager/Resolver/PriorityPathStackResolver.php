<?php

namespace AssetManager\Resolver;

use AssetManager\Resolver\PathStackResolver;
use Zend\Stdlib\PriorityQueue;

class PriorityPathStackResolver extends PathStackResolver
{
    /**
     * @var PriorityQueue|ResolverInterface[]
     */
    protected $paths;

    /**
    * Constructor.
    * Construct object and set a new PriorityQueue.
    */
    public function __construct()
    {
        $this->clearPaths();
    }

    /**
     * {@inheritDoc}
     */
    public function addPath($path)
    {
        $priority = 1;

        if (is_array($path)) {
            $priority   = $path['priority'];
            $path       = $path['path'];
        }

        $this->paths->insert(static::normalizePath($path), $priority);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * {@inheritDoc}
     */
    public function clearPaths()
    {
        $this->paths = new PriorityQueue();
    }
}
