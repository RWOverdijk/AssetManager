<?php

namespace AssetManager\Resolver;

use SplFileInfo;
use Zend\Stdlib\SplStack;
use AssetManager\Exception;

class PathStack implements ResolverInterface
{
    /**
     * @var SplStack
     */
    protected $paths;

    /**
     * Flag indicating whether or not LFI protection for rendering view scripts is enabled
     *
     * @var bool
     */
    protected $lfiProtectionOn = true;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->paths = new SplStack();
    }

    /**
     * Add many paths to the stack at once
     *
     * @param  array $paths
     * @return self
     */
    public function addPaths(array $paths)
    {
        foreach ($paths as $path) {
            $this->addPath($path);
        }

        return $this;
    }

    /**
     * Rest the path stack to the paths provided
     *
     * @param  SplStack|array            $paths
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setPaths($paths)
    {
        if ($paths instanceof SplStack) {
            $this->paths = $paths;

            return $this;
        } elseif (is_array($paths)) {
            $this->clearPaths();
            $this->addPaths($paths);

            return $this;
        }

        throw new Exception\InvalidArgumentException(
            "Invalid argument provided for \$paths, expecting either an array or SplStack object"
        );
    }

    /**
     * Normalize a path for insertion in the stack
     *
     * @param  string $path
     * @return string
     */
    protected static function normalizePath($path)
    {
        $path = rtrim($path, '/\\');
        $path .= DIRECTORY_SEPARATOR;

        return $path;
    }

    /**
     * Add a single path to the stack
     *
     * @param  string                    $path
     * @return self
     * @throws \InvalidArgumentException
     */
    public function addPath($path)
    {
        if (!is_string($path)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid path provided; must be a string, received %s',
                gettype($path)
            ));
        }

        $this->paths[] = static::normalizePath($path);

        return $this;
    }

    /**
     * Clear all paths
     *
     * @return void
     */
    public function clearPaths()
    {
        $this->paths = new SplStack();
    }

    /**
     * Returns stack of paths
     *
     * @return SplStack
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Set LFI protection flag
     *
     * @param  bool $flag
     * @return self
     */
    public function setLfiProtection($flag)
    {
        $this->lfiProtectionOn = (bool) $flag;

        return $this;
    }

    /**
     * Return status of LFI protection flag
     *
     * @return bool
     */
    public function isLfiProtectionOn()
    {
        return $this->lfiProtectionOn;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($name)
    {
        if ($this->isLfiProtectionOn() && preg_match('#\.\.[\\\/]#', $name)) {
            return null;
        }

        foreach ($this->paths as $path) {
            $file = new SplFileInfo($path . $name);

            if ($file->isReadable() && !$file->isDir()) {
                if ($filePath = $file->getRealPath()) {

                    return $filePath;
                }
            }
        }

        return null;
    }
}
