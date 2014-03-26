<?php

namespace AssetManager\Cache;

use Assetic\Cache\CacheInterface;
use AssetManager\Exception\RuntimeException;
use Zend\Stdlib\ErrorHandler;

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

        ErrorHandler::start();

        if (!is_dir($cacheDir)) {
            $umask = umask(0);
            mkdir($cacheDir, 0777, true);
            umask($umask);

            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        ErrorHandler::stop();

        if (!is_writable($cacheDir)) {
            throw new \RuntimeException('Unable to write file ' . $this->cachedFile());
        }

        // Use "rename" to achieve atomic writes
        $tmpFilePath = $cacheDir . '/' . uniqid('AssetManagerFilePathCache');
        file_put_contents($tmpFilePath, $value);
        rename($tmpFilePath, $this->cachedFile());
    }

    /**
     * {@inheritDoc}
     */
    public function remove($key)
    {
        ErrorHandler::start(\E_WARNING);

        $success = unlink($this->cachedFile());

        ErrorHandler::stop();

        if (false === $success) {
            throw new RuntimeException(sprintf('Could not remove key "%s"', $this->cachedFile()));
        }

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
