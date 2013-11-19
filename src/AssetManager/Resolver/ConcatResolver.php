<?php

namespace AssetManager\Resolver;

use Traversable;
use Zend\Stdlib\ArrayUtils;
use Assetic\Asset\AssetInterface;
use AssetManager\Asset\ConcatStringAsset;
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
     * @var ResolverInterface
     */
    protected $aggregateResolver;

    /**
     * @var AssetFilterManager The filterManager service.
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
        if (!is_array($concats) && !$concats instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s: expects an array or Traversable, received "%s"',
                __METHOD__,
                (is_object($concats) ? get_class($concats) : gettype($concats))
            ));
        }

        if ($concats instanceof Traversable) {
            $concats = ArrayUtils::iteratorToArray($concats);
        }

        $this->concats = $concats;
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

        if (!is_array($this->concats[$name])) {
            throw new Exception\RuntimeException(
                "Concat with name $name is not an an array."
            );
        }

        $stringAsset = new ConcatStringAsset();
        $mimeType   = null;
        $lastModified = 0;

        foreach ($this->concats[$name] as $asset) {

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

            $res->mimetype = $this->getMimeResolver()->getMimeType(
                $res->getSourceRoot().$res->getSourcePath()
            );

            if (null !== $mimeType && $res->mimetype !== $mimeType) {
                throw new Exception\RuntimeException(sprintf(
                    'Asset "%s" from collection "%s" doesn\'t have the expected mime-type "%s".',
                    $asset,
                    $name,
                    $mimeType
                ));
            }

            $this->getAssetFilterManager()->setFilters($asset, $res);
            $stringAsset->appendContent($res->dump());

            if ($res->getLastModified() > $lastModified) {
                $lastModified = $res->getLastModified();
            }
        }

        $stringAsset->setLastModified($lastModified);
        $stringAsset->mimetype = $mimeType;
        $this->getAssetFilterManager()->setFilters($name, $stringAsset);

        return $stringAsset;
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
