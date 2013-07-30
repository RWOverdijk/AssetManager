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
     * @var string
     */
    protected $cacheFile;

    /**
     * @var array
     */
    protected $storageOptions;

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return string
     */
    public function getCachePath()
    {
        return $this->cachePath;
    }

    /**
     * @return string
     */
    public function getCacheFile()
    {
        return $this->cacheFile;
    }

    /**
     * @return array
     */
    public function getStorageOptions()
    {
        return $this->storageOptions;
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
     * @param string $cachePath
     *
     * @return DynamicCollectionCacheOptions
     */
    public function setCachePath($cachePath)
    {
        $this->cachePath = $cachePath;
        return $this;
    }

    /**
     * @param string $cacheFile
     *
     * @return DynamicCollectionCacheOptions
     */
    public function setCacheFile($cacheFile)
    {
        $this->cacheFile = $cacheFile;
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
}
