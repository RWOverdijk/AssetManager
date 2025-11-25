<?php

namespace AssetManager\Resolver;

use Assetic\Asset\FileAsset;
use Assetic\Factory\Resource\DirectoryResource;
use AssetManager\Exception;
use AssetManager\Exception\InvalidArgumentException;
use AssetManager\Exception\RuntimeException;
use AssetManager\Service\MimeResolver;
use Laminas\Stdlib\SplStack;
use SplFileInfo;

/**
 * This resolver allows you to resolve from a stack of aliases to a path.
 */
class AliasPathStackResolver implements ResolverInterface, MimeResolverAwareInterface
{
    /**
     * @var array
     */
    protected array $aliases = [];

    /**
     * Flag indicating whether LFI protection for rendering view scripts is enabled
     *
     * @var bool
     */
    protected bool $lfiProtectionOn = true;

    /**
     * The mime resolver.
     *
     * @var MimeResolver
     */
    protected $mimeResolver;

    /**
     * Constructor
     *
     * Populate the array stack with a list of aliases and their corresponding paths
     *
     * @param array $aliases
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $aliases)
    {
        foreach ($aliases as $alias => $path) {
            $this->addAlias($alias, $path);
        }
    }

    /**
     * Add a single alias to the stack
     *
     * @param string $alias
     * @param string $path
     *
     * @throws InvalidArgumentException
     */
    private function addAlias($alias, $path): void
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid path provided; must be a string, received %s',
                    gettype($path),
                ),
            );
        }

        if (!is_string($alias)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid alias provided; must be a string, received %s',
                    gettype($alias),
                ),
            );
        }

        $this->aliases[$alias] = $this->normalizePath($path);
    }

    /**
     * Normalize a path for insertion in the stack
     *
     * @param string $path
     *
     * @return string
     */
    private function normalizePath($path): string
    {
        return rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
    }

    /**
     * Set the mime resolver
     *
     * @param MimeResolver $resolver
     */
    public function setMimeResolver(MimeResolver $resolver): void
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
     * Set LFI protection flag
     *
     * @param bool $flag
     *
     * @return self
     */
    public function setLfiProtection($flag): static
    {
        $this->lfiProtectionOn = (bool) $flag;

        return $this;
    }

    /**
     * Return status of LFI protection flag
     *
     * @return bool
     */
    public function isLfiProtectionOn(): bool
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

        foreach ($this->aliases as $alias => $path) {
            if (strpos($name, $alias) === false) {
                continue;
            }

            $filename = substr_replace($name, '', 0, strlen($alias));

            $file = new SplFileInfo($path . $filename);

            if ($file->isReadable() && !$file->isDir()) {
                $filePath = $file->getRealPath();
                $mimeType = $this->getMimeResolver()->getMimeType($filePath);
                $asset    = new FileAsset($filePath);

                $asset->mimetype = $mimeType;

                return $asset;
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function collect()
    {
        $collection = [];

        foreach ($this->aliases as $alias => $path) {
            $locations = new SplStack();
            $pathInfo  = new SplFileInfo($path);
            $locations->push($pathInfo);
            $basePath = $this->normalizePath($pathInfo->getRealPath());

            while (!$locations->isEmpty()) {
                /** @var SplFileInfo $pathInfo */
                $pathInfo = $locations->pop();
                if (!$pathInfo->isReadable()) {
                    throw new RuntimeException(sprintf('%s is not readable.', $pathInfo->getPath()));
                }
                if ($pathInfo->isDir()) {
                    foreach (new DirectoryResource($pathInfo->getRealPath()) as $resource) {
                        $locations->push(new SplFileInfo($resource));
                    }
                } else {
                    $collection[] = $alias . substr($pathInfo->getRealPath(), strlen($basePath));
                }
            }
        }

        return array_unique($collection);
    }
}
