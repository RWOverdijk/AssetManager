<?php

namespace AssetManager\Cache;

use Assetic\Contracts\Cache\CacheInterface;
use Laminas\Cache\Storage\StorageInterface;

/**
 * Laminas Cache Storage Adapter for Assetic
 */
class LaminasCacheAdapter implements CacheInterface
{

    /** @var StorageInterface */
    protected $laminasCache;

    /**
     * Constructor
     *
     * @param StorageInterface $laminasCache Laminas Configured Cache Storage
     */
    public function __construct(StorageInterface $laminasCache)
    {
        $this->laminasCache = $laminasCache;
    }

    /**
     * {@inheritDoc}
     */
    public function has($key)
    {
        return $this->laminasCache->hasItem($key);
    }

    /**
     * {@inheritDoc}
     */
    public function get($key)
    {
        return $this->laminasCache->getItem($key);
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value)
    {
        return $this->laminasCache->setItem($key, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($key)
    {
        return $this->laminasCache->removeItem($key);
    }
}
