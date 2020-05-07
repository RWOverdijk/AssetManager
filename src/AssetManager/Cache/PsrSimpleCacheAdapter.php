<?php

declare(strict_types=1);

namespace AssetManager\Cache;

use Assetic\Contracts\Cache\CacheInterface;
use Psr\SimpleCache\CacheInterface as SimpleCache;

/**
 * PSR SimpleCache Adapter for Assetic
 */
class PsrSimpleCacheAdapter implements CacheInterface
{

    /** @var SimpleCache */
    protected $cache;

    /** @var int|null */
    protected $ttl;

    /**
     * Constructor
     *
     * @param SimpleCache $cache Laminas Configured Cache Storage
     * @param int|null $ttl
     */
    public function __construct(SimpleCache $cache, ?int $ttl = null)
    {
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    /**
     * {@inheritDoc}
     */
    public function has($key)
    {
        return $this->cache->has($key);
    }

    /**
     * {@inheritDoc}
     */
    public function get($key)
    {
        return $this->cache->get($key);
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value)
    {
        return $this->cache->set($key, $value, $this->ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($key)
    {
        return $this->cache->delete($key);
    }
}
