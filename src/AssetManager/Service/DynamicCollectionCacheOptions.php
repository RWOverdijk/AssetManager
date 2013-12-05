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
     * @var array
     */
    protected $storageOptions;

    /**
     * @var string
     */
    protected $cacheKey;

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

        /**
     * @return array
     */
    public function getStorageOptions()
    {
        return $this->storageOptions;
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cacheKey;
    }

    /**
     * @param boolean $enabled
     *
     * @return DynamicCollectionCacheOptions
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @param srray $storageOptions
     *
     * @return DynamicCollectionCacheOptions
     */
    public function setStorageOptions(array $storageOptions)
    {
        $this->storageOptions = $storageOptions;
        return $this;
    }

    /**
     * @param string $cacheKey
     * @return DynamicCollectionCacheOptions
     */
    public function setCacheKey($cacheKey)
    {
        $this->cacheKey = $cacheKey;
        return $this;
    }
}
