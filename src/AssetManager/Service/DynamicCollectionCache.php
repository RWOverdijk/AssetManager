<?php
namespace AssetManager\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;

class DynamicCollectionCache implements ServiceLocatorAwareInterface
{
    use \Zend\ServiceManager\ServiceLocatorAwareTrait;
    use \AssetManager\ServiceManager\ServiceTrait;

    /**
     * @var DynamicCollectionCacheOptions
     */
    protected $options;

    /**
     * @var array
     */
    protected $collections;

    public function __construct(DynamicCollectionCacheOptions $options)
    {
        $this->options = $options;
    }

    /**
     * Get the cached dynamic collections
     *
     * @return array
     */
    public function getCollections()
    {
        if (false === $this->options->getEnabled()) {
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
        if (false === $this->options->getEnabled()) {
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
     * Loads the collections from the cache folder
     */
    protected function loadCollections()
    {
        $dynamicCollections = $this->getCacheFileName();
        if (!file_exists($dynamicCollections)) {
            $this->collections = array();
            return;
        }
        $fromFile = include $dynamicCollections;;
        if (!is_array($fromFile)) {
            $fromFile = array();
        }
        $this->collections = $fromFile;
    }

    /**
     * Gets the cache file name
     *
     * @return string
     */
    protected function getCacheFileName()
    {
        return $this->options->getCachePath() .
            DIRECTORY_SEPARATOR . 'dynamic_collections.php';
    }

    /**
     * Try to save the current collections array to cache file
     *
     * When the file is in use this method returns false
     *
     * @return boolean true on success false on failure
     */
    protected function saveCollections()
    {
        $file = $this->getCacheFileName();
        $toWrite =  "<?php\n" .
            'return ' . var_export($this->collections, true) . ";\n";
        if (file_exists($file)) {
            $fp = fopen($this->getCacheFileName(), 'r+');
            if (false === flock($fp, LOCK_EX)) {
                fclose($fp);
                return false;
            }
            ftruncate($fp, 0);
            fwrite($fp, $toWrite);
            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);
        } else {
            $fp = fopen($this->getCacheFileName(), 'w');
            fwrite($fp, $toWrite);
            fclose($fp);
        }
        return true;
    }
}
