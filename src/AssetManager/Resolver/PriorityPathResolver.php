<?php

namespace AssetManager\Resolver;

use SplFileInfo;
use Zend\Stdlib\PriorityQueue;
use AssetManager\Exception;

class PriorityPathResolver implements ResolverInterface
{
    /**
     * @var PriorityQueue|ResolverInterface[]
     */
    protected $paths;

    /**
     * Flag indicating whether or not LFI protection for rendering view scripts is enabled
     *
     * @var bool
     */
    protected $lfiProtectionOn = true;

    /**
    * Constructor.
    * Construct object and set a new PriorityQueue.
    */
    public function __construct()
    {
        $this->clearPaths();
    }

    /**
     * {@inheritDoc}
     */
    public function addPath($path)
    {
        $priority = 1;

        if (is_array($path)) {
            $priority   = $path['priority'];
            $path       = $path['path'];
        }

        $this->paths->insert(static::normalizePath($path), $priority);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * {@inheritDoc}
     */
    public function clearPaths()
    {
        $this->paths = new PriorityQueue();
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
     * @throws Exception\InvalidArgumentException
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

        foreach ($this->getPaths() as $path) {
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
