<?php

namespace AssetManager\Resolver;

use Traversable;
use Zend\Stdlib\ArrayUtils;
use Assetic\Asset\AssetInterface;
use AssetManager\Asset\AggregateAsset;
use AssetManager\Exception;
use AssetManager\Service\AssetFilterManagerAwareInterface;
use AssetManager\Service\AssetFilterManager;
use AssetManager\Service\MimeResolver;

/**
 * This resolver allows the resolving of concatenated files.
 * Concatted files are added as an StringAsset and filters get applied to concatenated string.
 */
class ConcatResolver implements
    ResolverInterface,
    AggregateResolverAwareInterface,
    AssetFilterManagerAwareInterface,
    MimeResolverAwareInterface
{
    /**
     * @var null|ResolverInterface
     */
    protected $aggregateResolver;

    /**
     * @var null|AssetFilterManager The filterManager service.
     */
    protected $filterManager;

    /**
     * @var array the concats
     */
    protected $concats = array();

    /**
     * @var MimeResolver The mime resolver.
     */
    protected $mimeResolver;

    /**
     * Constructor
     *
     * Instantiate and optionally populate concats.
     *
     * @param array|Traversable $concats
     */
    public function __construct($concats = array())
    {
        $this->setConcats($concats);
    }

    /**
     * Set the mime resolver
     *
     * @param MimeResolver $resolver
     */
    public function setMimeResolver(MimeResolver $resolver)
    {
        $this->mimeResolver = $resolver;
    }

    /**
     * Get the mime resolver
     *
     * @return MimeResolver
     */
    public function getMimeResolver()
    {
        return $this->mimeResolver;
    }

    /**
     * Set (overwrite) concats
     *
     * Concats should be arrays or Traversable objects with name => path pairs
     *
     * @param  array|Traversable                  $concats
     * @throws Exception\InvalidArgumentException
     */
    public function setConcats($concats)
    {
        $this->concats = ArrayUtils::iteratorToArray($concats);
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
     * Retrieve the concats
     *
     * @return array
     */
    public function getConcats()
    {
        return $this->concats;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($name)
    {
        if (!isset($this->concats[$name])) {
            return null;
        }

        $resolvedAssets = array();

        foreach ((array) $this->concats[$name] as $assetName) {

            $resolvedAsset = $this->getAggregateResolver()->resolve((string) $assetName);

            if (!$resolvedAsset instanceof AssetInterface) {
                throw new Exception\RuntimeException(
                    sprintf(
                        'Asset "%s" from collection "%s" can\'t be resolved '
                        .'to an Asset implementing Assetic\Asset\AssetInterface.',
                        $assetName,
                        $name
                    )
                );
            }

            $resolvedAsset->mimetype = $this->getMimeResolver()->getMimeType(
                $resolvedAsset->getSourceRoot() . $resolvedAsset->getSourcePath()
            );

            $this->getAssetFilterManager()->setFilters($assetName, $resolvedAsset);

            $resolvedAssets[] = $resolvedAsset;
        }
        $aggregateAsset = new AggregateAsset($resolvedAssets);
        $this->getAssetFilterManager()->setFilters($name, $aggregateAsset);
        $aggregateAsset->setTargetPath($name);

        return $aggregateAsset;
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

    /**
     * {@inheritDoc}
     */
    public function collect()
    {
        return array_keys($this->concats);
    }
}
