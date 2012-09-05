<?php

namespace AssetManager\Service;

use Assetic\Asset\AssetInterface;
use Assetic\Asset\AssetCache;
use Assetic\Cache\CacheInterface;
use Assetic\Filter;
use Assetic\Cache;
use AssetManager\Cache\FilePathCache;
use AssetManager\Exception;
use AssetManager\Resolver\ResolverInterface;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;
use Zend\Http\PhpEnvironment\Request;

/**
 * @category    AssetManager
 * @package     AssetManager
 */
class AssetManager
{
    /**
     * @var ResolverInterface
     */
    protected $resolver;

    /**
     * @var AssetInterface The asset
     */
    protected $asset;

    /**
     * @var string The requested path
     */
    protected $path;

    /**
     * @var array The asset_manager configuration
     */
    protected $config;

    /**
     * Constructor
     *
     * @param ResolverInterface $resolver
     * @param array             $config
     *
     * @return AssetManager
     */
    public function __construct($resolver, $config = array())
    {
        $this->setResolver($resolver);
        $this->setConfig($config);
    }

    /**
     * Set the config
     *
     * @param array $config
     */
    protected function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * Check if the request resolves to an asset.
     *
     * @param    RequestInterface $request
     * @return   boolean
     */
    public function resolvesToAsset(RequestInterface $request)
    {
        if (null === $this->asset) {
            $this->asset = $this->resolve($request);
        }

        return (bool)$this->asset;
    }

    /**
     * Set the resolver to use in the asset manager
     *
     * @param ResolverInterface $resolver
     */
    public function setResolver(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Get the resolver used by the asset manager
     *
     * @return ResolverInterface
     */
    public function getResolver()
    {
        return $this->resolver;
    }

    /**
     * See if there are filters for the asset, and if so, set them on the asset.
     *
     * @return void
     */
    protected function setFilters()
    {
        if (empty($this->config['filters'][$this->path])) {
            return;
        }

        foreach ($this->config['filters'][$this->path] as $filter) {

            if ($filter['filter'] instanceof Filter\FilterInterface) {
                $filterInstance = $filter['filter'];
                $this->asset->ensureFilter($filterInstance);
                continue;
            }

            $filterClass = $filter['filter'];

            if (!is_subclass_of($filterClass, 'Assetic\Filter\FilterInterface', true)) {
                $filterClass .= (substr($filterClass, -6) === 'Filter') ? '' : 'Filter';
                $filterClass  = 'Assetic\Filter\\' . $filterClass;
            }

            if (!class_exists($filterClass)) {
                throw new Exception\RuntimeException(
                    'No filter found for ' . $filter['filter']
                );
            }

            $filterInstance = new $filterClass;

            $this->asset->ensureFilter($filterInstance);
        }
    }

    /**
     * Set the cache (if any) on the asset
     *
     * @return void
     */
    protected function setCache()
    {
        $caching = null;

        if (!empty($this->config['caching'][$this->path])) {
            $caching = $this->config['caching'][$this->path];
        } elseif (!empty($this->config['caching']['default'])) {
            $caching = $this->config['caching']['default'];
        }

        if (null === $caching) {
            return;
        }

        if (empty($caching['cache'])) {
            return;
        }

        $cacher = null;

        if (is_callable($caching['cache'])) {
            $cacher = $caching['cache']($this->path);
        } else {
            $filename   = $this->path;
            // @codeCoverageIgnoreStart
            $factories  = array(
                'FilesystemCache' => function($options) {
                    $dir = $options['dir'];
                    return new Cache\FilesystemCache($dir);
                },
                'ApcCache' => function($options) {
                    return new Cache\ApcCache();
                },
                'FilePathCache' => function($options) use ($filename) {
                    $dir = $options['dir'];
                    return new FilePathCache($dir, $filename);
                }
            );
            // @codeCoverageIgnoreEnd

            $type = $caching['cache'];
            $type .= (substr($type, -5) === 'Cache') ? '' : 'Cache';

            if (!isset($factories[$type])) {
                return;
            }

            $options = empty($caching['options']) ? array() : $caching['options'];

            $cacher = $factories[$type]($options);
        }

        if (!$cacher instanceof CacheInterface) {
            return;
        }

        $assetCache             = new AssetCache($this->asset, $cacher);
        $assetCache->mimetype   = $this->asset->mimetype;
        $this->asset            = $assetCache;
    }

    /**
     * Set the asset on the response, including headers and content.
     *
     * @param    ResponseInterface $response
     * @return   ResponseInterface
     * @throws   Exception\RuntimeException
     */
    public function setAssetOnResponse(ResponseInterface $response)
    {
        if (!$this->asset instanceof AssetInterface) {
            throw new Exception\RuntimeException(
                'Unable to set asset on response. Request has not been resolved to an asset.'
            );
        }

        // @todo: Create Asset wrapper for mimetypes
        if (empty($this->asset->mimetype)) {
            throw new Exception\RuntimeException('Expected property "mimetype" on asset.');
        }

        $this->setFilters();
        $this->setCache();

        $mimeType       = $this->asset->mimetype;
        $assetContents  = $this->asset->dump();

        // @codeCoverageIgnoreStart
        if (function_exists('mb_strlen')) {
            $contentLength = mb_strlen($assetContents, '8bit');
        } else {
            $contentLength = strlen($assetContents);
        }
        // @codeCoverageIgnoreEnd

        $response->getHeaders()
                 ->addHeaderLine('Content-Transfer-Encoding', 'binary')
                 ->addHeaderLine('Content-Type', $mimeType)
                 ->addHeaderLine('Content-Length', $contentLength);

        $response->setContent($assetContents);

        return $response;
    }

    /**
     * Resolve the request to a file.
     *
     * @param RequestInterface $request
     *
     * @return mixed false when not found, AssetInterface when resolved.
     */
    protected function resolve(RequestInterface $request)
    {
        if (!$request instanceof Request) {
            return false;
        }

        /* @var $request Request */
        /* @var $uri \Zend\Uri\UriInterface */
        $uri        = $request->getUri();
        $fullPath   = $uri->getPath();
        $path       = substr($fullPath, strlen($request->getBasePath()) + 1);
        $this->path = $path;
        $asset      = $this->getResolver()->resolve($path);

        if (!$asset instanceof AssetInterface) {
            return false;
        }

        return $asset;
    }
}
