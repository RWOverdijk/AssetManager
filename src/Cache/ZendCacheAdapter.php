<?php

namespace AssetManager\Cache;

use Assetic\Cache\CacheInterface;
use Zend\Cache\Storage\StorageInterface;

/**
 * Zend Cache Storage Adapter for Assetic
 */
class ZendCacheAdapter implements CacheInterface
{

    /** @var StorageInterface */
    protected $zendCache;

    /**
     * Constructor
     *
     * @param StorageInterface $zendCache Zend Configured Cache Storage
     */
    public function __construct(StorageInterface $zendCache)
    {
        $this->zendCache = $zendCache;
    }

    /**
     * {@inheritDoc}
     */
    public function has($key)
    {
        return $this->zendCache->hasItem($key);
    }

    /**
     * {@inheritDoc}
     */
    public function get($key)
    {
        return $this->zendCache->getItem($key);
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value)
    {
        return $this->zendCache->setItem($key, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($key)
    {
        return $this->zendCache->removeItem($key);
    }
}
