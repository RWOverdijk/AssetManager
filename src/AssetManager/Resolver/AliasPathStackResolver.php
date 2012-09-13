<?php

namespace AssetManager\Resolver;

use SplFileInfo;
use Traversable;
use Zend\Stdlib\SplStack;
use Assetic\Asset\FileAsset;
use AssetManager\Exception;
use AssetManager\Service\MimeResolver;

/**
 * This resolver allows you to resolve from a stack of paths.
 */
class AliasPathStackResolver implements ResolverInterface, MimeResolverAwareInterface
{
    /**
     * @var SplStack
     */
    protected $aliases = array();

    /**
     * Flag indicating whether or not LFI protection for rendering view scripts is enabled
     *
     * @var bool
     */
    protected $lfiProtectionOn = true;

    /**
     * The mime resolver.
     *
     * @var MimeResolver
     */
    protected $mimeResolver;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->aliases = array();
    }

    /**
     * Set the mime resolver
     *
     * @param MimeResolver $resolver
     */
    public function setMimeResolver(MimeResolver $resolver)
    {
        $this->mimeResolver = $resolver;
    }

    /**
     * Get the mime resolver
     *
     * @return MimeResolver
     */
    public function getMimeResolver()
    {
        return $this->mimeResolver;
    }

    /**
     * Add many paths to the stack at once
     *
     * @param array|Traversable $aliases
     */
    public function addAliases($aliases)
    {
        foreach ($aliases as $relPath => $alias) {
            $this->addAlias($relPath, $alias);
        }
    }

    /**
     * Rest the path stack to the paths provided
     *
     * @param  Traversable|array                  $aliases
     * @throws Exception\InvalidArgumentException
     */
    public function setAliases($aliases)
    {
        if (!is_array($aliases) && !$aliases instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid argument provided for $paths, expecting either an array or Traversable object, "%s" given',
                is_object($aliases) ? get_class($aliases) : gettype($aliases)
            ));
        }

        $this->clearAliases();
        $this->addAliases($aliases);
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

        return $path;
    }

    /**
     * Add a single path to the stack
     *
     * @param  string                             $relPath
     * @param  array                              $alias
     * @throws Exception\InvalidArgumentException
     */
    public function addAlias($relPath, $alias)
    {
        if (!is_array($alias)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid path provided; must be an array, received %s',
                gettype($alias)
            ));
        }

        foreach($alias as $path) {
            $this->aliases[$relPath][] = $this->normalizePath($path);
        }
    }

    /**
     * Clear all paths
     *
     * @return void
     */
    public function clearAliases()
    {
        $this->aliases = new SplStack();
    }

    /**
     * Returns stack of paths
     *
     * @return SplStack
     */
    public function getAliases()
    {
        return $this->aliases;
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

        foreach ($this->getAliases() as $relPath => $paths) {

            if(strstr($name, $relPath)) {

                $aliasedName = str_replace($relPath, '', $name);

                foreach ($paths as $path) {

                    $file = new SplFileInfo($path . $aliasedName);

                    if ($file->isReadable() && !$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $mimeType = $this->getMimeResolver()->getMimeType($filePath);
                        $asset    = new FileAsset($filePath);

                        $asset->mimetype = $mimeType;

                        return $asset;
                    }
                }
            }
        }

        return null;
    }
}
