<?php

namespace AssetManager\Cache;

use Assetic\Cache\CacheInterface;

/**
 * A file path cache. Same as FilesystemCache, except for the fact that this will create the
 * directories recursively, in stead of using a hash.
 */
class FilePathCache implements CacheInterface
{
    /**
     * @var string Holds the cache directory.
     */
    protected $dir;

    /**
     * @var string The filename we'll be caching for.
     */
    protected $filename;

    /**
     * @var string Holds the cachedFile string
     */
    protected $cachedFile;

    /**
     * Constructor
     *
     * @param string $dir       The directory to cache in
     * @param string $filename  The filename we'll be caching for.
     */
    public function __construct($dir, $filename)
    {
        $this->dir      = $dir;
        $this->filename = $filename;
    }

    /**
     * {@inheritDoc}
     */
    public function has($key)
    {
        return file_exists($this->cachedFile());
    }

    /**
     * {@inheritDoc}
     */
    public function get($key)
    {
        $path = $this->cachedFile();

        if (!file_exists($path)) {
            throw new \RuntimeException('There is no cached value for ' . $this->filename);
        }

        return file_get_contents($path);
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value)
    {
        $cacheDir = dirname($this->cachedFile());

        set_error_handler(function($errno, $errstr) {
            if ($errstr !== 'mkdir(): File exists') {
                throw new \RuntimeException($errstr);
            }
        });

        if (!is_dir($cacheDir)) {
            $umask = umask(0);
            mkdir($cacheDir, 0777, true);
            umask($umask);

            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        restore_error_handler();

        if (!is_writable($cacheDir)) {
            throw new \RuntimeException('Unable to write file ' . $this->cachedFile());
        }

        file_put_contents($this->cachedFile(), $value);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($key)
    {
        set_error_handler(function($errno, $errstr) {
            throw new \RuntimeException($errstr);
        });

        $success = unlink($this->cachedFile());

        restore_error_handler();

        return $success;
    }

    /**
     * Get the path-to-file.
     * @return string Cache path
     */
    protected function cachedFile()
    {
        if (null === $this->cachedFile) {
            $this->cachedFile = rtrim($this->dir, '/') . '/' . ltrim($this->filename, '/');
        }

        return $this->cachedFile;
    }
}
