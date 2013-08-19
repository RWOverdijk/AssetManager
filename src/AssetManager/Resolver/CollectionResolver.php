<?php

namespace AssetManager\Resolver;

use Traversable;
use Zend\Stdlib\ArrayUtils;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\AssetInterface;
use AssetManager\Exception;
use AssetManager\Resolver\ResolverInterface;
use AssetManager\Service\AssetFilterManagerAwareInterface;
use AssetManager\Service\AssetFilterManager;

/**
 * This resolver allows the resolving of collections.
 * Collections are strictly checked by mime-type,
 * and added to an AssetCollection when all checks passed.
 */
class CollectionResolver implements
    ResolverInterface,
    AggregateResolverAwareInterface,
    AssetFilterManagerAwareInterface
{
    /**
     * @var ResolverInterface
     */
    protected $aggregateResolver;

    /**
     * @var AssetFilterManager The filterManager service.
     */
    protected $filterManager;

    /**
     * @var array the collections
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
     * Collections should be arrays or Traversable objects with name => path pairs
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
     * Set the aggregate resolver.
     *
     * @param ResolverInterface $aggregateResolver
     */
    public function setAggregateResolver(ResolverInterface $aggregateResolver)
    {
        $this->aggregateResolver = $aggregateResolver;
    }

    /**
     * Get the aggregate resolver.
     *
     * @return ResolverInterface
     */
    public function getAggregateResolver()
    {
        return $this->aggregateResolver;
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
        if (!isset($this->collections[$name])) {
            return null;
        }

        if (!is_array($this->collections[$name])) {
            throw new Exception\RuntimeException(
                "Collection with name $name is not an an array."
            );
        }

        $collection = new AssetCollection;
        $mimeType   = null;
        $collection->setTargetPath($name);

        foreach ($this->collections[$name] as $asset) {

            if (!is_string($asset)) {
                throw new Exception\RuntimeException(
                    'Asset should be of type string. got ' . gettype($asset)
                );
            }
            if (null === ($res = $this->getAggregateResolver()->resolve($asset))) {
                throw new Exception\RuntimeException("Asset '$asset' could not be found.");
            }

            if (!$res instanceof AssetInterface) {
                throw new Exception\RuntimeException(
                    "Asset '$asset' does not implement Assetic\\Asset\\AssetInterface."
                );
            }

            if (null !== $mimeType && $res->mimetype !== $mimeType) {
                throw new Exception\RuntimeException(sprintf(
                    'Asset "%s" from collection "%s" doesn\'t have the expected mime-type "%s".',
                    $asset,
                    $name,
                    $mimeType
                ));
            }

            $mimeType = $res->mimetype;

            $this->getAssetFilterManager()->setFilters($asset, $res);

            $collection->add($res);
        }

        $collection->mimetype = $mimeType;

        return $collection;
    }

    /**
     * Set the AssetFilterManager.
     *
     * @param AssetFilterManager $filterManager
     */
    public function setAssetFilterManager(AssetFilterManager $filterManager)
    {
        $this->filterManager = $filterManager;
    }

    /**
     * Get the AssetFilterManager
     *
     * @return AssetFilterManager
     */
    public function getAssetFilterManager()
    {
        return $this->filterManager;
    }
}
