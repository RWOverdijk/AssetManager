<?php
namespace AssetManager\Service;

use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\StorageInterface;

class DynamicCollectionCache
{
    /**
     * @var DynamicCollectionCacheOptions
     */
    protected $options;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var array
     */
    protected $collections;

    public function __construct(
        DynamicCollectionCacheOptions $options
    ) {
        $this->options = $options;
    }

    /**
     * Get the cached dynamic collections
     *
     * @return array
     */
    public function getCollections()
    {
        if (false === $this->options->isEnabled()) {
            return array();
        }
        if (null === $this->collections) {
            $this->loadCollections();
        }
        return $this->collections;
    }

    /**
     * Adds a dynamic collection to cache
     *
     * @param string $collectionIdentifier
     * @param array  $collection
     *
     * @return string|boolean The collection identifier or false when disabled
     */
    public function addDynamicCollection(
              $collectionIdentifier,
        array $collection
    ) {
        if (false === $this->options->isEnabled()) {
            return false;
        }
        if (null === $this->collections) {
            $this->loadCollections();
        }
        if (isset($this->collections[$collectionIdentifier])) {
            return true;
        }
        $this->collections[$collectionIdentifier] = $collection;
        return $this->saveCollections();
    }

    /**
     * @return StorageInterface
     */
    private function getStorage()
    {
        if ($this->storage instanceof StorageInterface) {
            return $this->storage;
        }
        $this->storage = StorageFactory::factory(
            $this->options->getStorageOptions()
        );
        return $this->storage;
    }

    /**
     * Loads the collections from the cache folder
     */
    protected function loadCollections()
    {
        $this->collections = (
            $this->getStorage()->getItem($this->getCacheIdentifier()) ?:
            array()
        );
    }

    /**
     * Gets the cache file name
     *
     * @return string
     */
    protected function getCacheIdentifier()
    {
        return $this->options->getCachePath() .
            DIRECTORY_SEPARATOR .
            $this->options->getCacheFile();
    }

    /**
     * Try to save the current collections array to cache
     *
     * When setting fails this method returns false
     *
     * @return boolean true on success false on failure
     */
    protected function saveCollections()
    {
        return $this->getStorage()->setItem(
            $this->getCacheIdentifier(),
            $this->collections
        );
    }
}
