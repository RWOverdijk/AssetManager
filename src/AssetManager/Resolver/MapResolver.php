<?php

namespace AssetManager\Resolver;

use Traversable;
use Zend\Stdlib\ArrayUtils;

class MapResolver implements ResolverInterface
{
    /**
     * @var array
     */
    protected $map = array();

    /**
     * Constructor
     *
     * Instantiate and optionally populate template map.
     *
     * @param array|Traversable $map
     */
    public function __construct($map = array())
    {
        $this->setMap($map);
    }

    /**
     * Set (overwrite) template map
     *
     * Maps should be arrays or Traversable objects with name => path pairs
     *
     * @param  array|Traversable $map
     * @return self
     */
    public function setMap($map)
    {
        if (!is_array($map) && !$map instanceof Traversable) {
            throw new \InvalidArgumentException(sprintf(
                '%s: expects an array or Traversable, received "%s"',
                __METHOD__,
                (is_object($map) ? get_class($map) : gettype($map))
            ));
        }

        if ($map instanceof Traversable) {
            $map = ArrayUtils::iteratorToArray($map);
        }

        $this->map = $map;

        return $this;
    }

    /**
     * Retrieve the template map
     *
     * @return array
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($name)
    {
        return isset($this->map[$name]) ? $this->map[$name] : null;
    }
}
