<?php

namespace AssetManager\Resolver;

use Traversable;
use Zend\Stdlib\ArrayUtils;
use AssetManager\Exception;

class CollectionResolver implements ResolverInterface
{
    /**
     * @var array
     */
    protected $collections = array();

    /**
     * Constructor
     *
     * Instantiate and optionally populate collections.
     *
     * @param array|Traversable $collections
     */
    public function __construct($collections = array())
    {
        $this->setCollections($collections);
    }

    /**
     * Set (overwrite) collections
     *
     * Collectionss should be arrays or Traversable objects with name => path pairs
     *
     * @param  array|Traversable                  $collections
     * @throws Exception\InvalidArgumentException
     */
    public function setCollections($collections)
    {
        if (!is_array($collections) && !$collections instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s: expects an array or Traversable, received "%s"',
                __METHOD__,
                (is_object($collections) ? get_class($collections) : gettype($collections))
            ));
        }

        if ($collections instanceof Traversable) {
            $collections = ArrayUtils::iteratorToArray($collections);
        }

        $this->collections = $collections;
    }

    /**
     * Retrieve the collections
     *
     * @return array
     */
    public function getCollections()
    {
        return $this->collections;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($name)
    {
        return isset($this->collections[$name]) ? $this->collections[$name] : null;
    }
}
