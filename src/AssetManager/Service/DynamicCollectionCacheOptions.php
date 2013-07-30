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
    protected $cackeKey;

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
    public function getCackeKey()
    {
        return $this->cackeKey;
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
     * @param string $cackeKey
     * @return DynamicCollectionCacheOptions
     */
    public function setCackeKey($cackeKey)
    {
        $this->cackeKey = $cackeKey;
        return $this;
    }
}
