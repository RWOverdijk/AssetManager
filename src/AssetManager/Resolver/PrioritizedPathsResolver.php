<?php

namespace AssetManager\Resolver;

use Traversable;
use ArrayAccess;
use SplFileInfo;
use Zend\Stdlib\PriorityQueue;
use AssetManager\Exception;

class PrioritizedPathsResolver implements ResolverInterface
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
        $this->paths = new PriorityQueue();
    }

    /**
     * {@inheritDoc}
     */
    public function addPath($path)
    {
        if (is_string($path)) {
            $this->paths->insert($this->normalizePath($path), 1);

            return;
        }

        if (!is_array($path) && !$path instanceof ArrayAccess) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Provided path must be an array or an instance of ArrayAccess, %s given',
                is_object($path) ? get_class($path) : gettype($path)
            ));
        }

        if (isset($path['priority']) && isset($path['path'])) {
            $this->paths->insert($this->normalizePath($path['path']), $path['priority']);

            return;
        }

        throw new Exception\InvalidArgumentException('Provided array must contain both keys "priority" and "path"');
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
     * @param  array|Traversable $paths
     * @return self
     */
    public function addPaths($paths)
    {
        foreach ($paths as $path) {
            $this->addPath($path);
        }
    }

    /**
     * Rest the path stack to the paths provided
     *
     * @param  Traversable|array                  $paths
     * @throws Exception\InvalidArgumentException
     */
    public function setPaths($paths)
    {
        if (!is_array($paths) && !$paths instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid argument provided for $paths, expecting either an array or Traversable object, "%s" given',
                is_object($paths) ? get_class($paths) : gettype($paths)
            ));
        }

        $this->clearPaths();
        $this->addPaths($paths);
    }

    /**
     * Normalize a path for insertion in the stack
     *
     * @param  string $path
     * @return string
     */
    protected function normalizePath($path)
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
                return $file->getRealPath();
            }
        }

        return null;
    }
}
