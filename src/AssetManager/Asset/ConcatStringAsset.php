<?php

namespace AssetManager\Asset;

use Assetic\Filter\FilterInterface;
use Assetic\Asset\BaseAsset;

/**
 * Represents a concatented string asset.
 */
class ConcatStringAsset extends BaseAsset
{
    private $lastModified;

    /**
     * Constructor.
     *
     * @param string $content    The content of the asset
     * @param array  $filters    Filters for the asset
     * @param string $sourceRoot The source asset root directory
     * @param string $sourcePath The source asset path
     */
    public function __construct($content = '', $filters = array(), $sourceRoot = null, $sourcePath = null)
    {
        $this->setContent($content);

        parent::__construct($filters, $sourceRoot, $sourcePath);
    }

    /**
     * load asset
     *
     * @param FilterInterface $additionalFilter
     */
    public function load(FilterInterface $additionalFilter = null)
    {
        $this->doLoad($this->getContent(), $additionalFilter);
    }

    /**
     * set last modified value of asset
     *
     * this is useful for cache mechanism detection id file has changed
     *
     * @param $lastModified
     */
    public function setLastModified($lastModified)
    {
        $this->lastModified = $lastModified;
    }

    /**
     * get last modified value from asset
     *
     * @return int|null
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * Append content to current content
     *
     * @param string $content
     *
     * @return $this
     */
    public function appendContent($content)
    {
        $this->setContent(
            $this->getContent().$content
        );
    }
}
