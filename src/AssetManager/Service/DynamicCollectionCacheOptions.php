<?php
namespace AssetManager\Service;

use Zend\Stdlib\AbstractOptions;

class DynamicCollectionCacheOptions extends AbstractOptions
{
    /**
     * @var boolean
     */
    protected $enabled = false;

    /**
     * @var string
     */
    protected $cachePath;

    /**
     * @return boolean
     */
    public function getEnabled() {
        return $this->enabled;
    }

    /**
     * @return string
     */
    public function getCachePath() {
        return $this->cachePath;
    }

    /**
     * @param boolean $enabled
     *
     * @return DynamicCollectionCacheOptions
     */
    public function setEnabled($enabled) {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @param string $cachePath
     *
     * @return DynamicCollectionCacheOptions
     */
    public function setCachePath($cachePath) {
        $this->cachePath = $cachePath;
        return $this;
    }
}
