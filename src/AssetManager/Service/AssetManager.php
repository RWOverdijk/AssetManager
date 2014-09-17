<?php

namespace AssetManager\Service;

use Assetic\Asset\AssetInterface;
use AssetManager\Exception;
use AssetManager\Resolver\ResolverInterface;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;
use Zend\Http\PhpEnvironment\Request;

/**
 * @category    AssetManager
 * @package     AssetManager
 */
class AssetManager implements
    AssetFilterManagerAwareInterface,
    AssetCacheManagerAwareInterface
{
    /**
     * @var ResolverInterface
     */
    protected $resolver;

    /**
     * @var AssetFilterManager The AssetFilterManager service.
     */
    protected $filterManager;

    /**
     * @var AssetCacheManager The AssetCacheManager service.
     */
    protected $cacheManager;

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
     * @var bool Whether this instance has at least one asset successfully set on response
     */
    protected $assetSetOnResponse = false;

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
     * Returns true if this instance of asset manager has at least one asset successfully set on response
     *
     * @return bool
     */
    public function assetSetOnResponse()
    {
        return $this->assetSetOnResponse;
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

        $this->getAssetFilterManager()->setFilters($this->path, $this->asset);

        $this->asset    = $this->getAssetCacheManager()->setCache($this->path, $this->asset);
        $mimeType       = $this->asset->mimetype;
        $assetContents  = $this->asset->dump();

        // @codeCoverageIgnoreStart
        if (function_exists('mb_strlen')) {
            $contentLength = mb_strlen($assetContents, '8bit');
        } else {
            $contentLength = strlen($assetContents);
        }
        // @codeCoverageIgnoreEnd

        if (!empty($this->config['clear_output_buffer']) && $this->config['clear_output_buffer']) {
            // Only clean the output buffer if it's turned on and something
            // has been buffered.
            if (ob_get_length() > 0) {
                ob_clean();
            }
        }

        $response->getHeaders()
                 ->addHeaderLine('Content-Transfer-Encoding', 'binary')
                 ->addHeaderLine('Content-Type', $mimeType)
                 ->addHeaderLine('Content-Length', $contentLength);

        $response->setContent($assetContents);

        $this->assetSetOnResponse = true;

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

    /**
     * Set the AssetFilterManager.
     *
     * @param AssetFilterManager $filterManager
     */
    public function setAssetFilterManager(AssetFilterManager $filterManager)
    {
        $this->filterManager = $filterManager;
    }

    /**
     * Get the AssetFilterManager
     *
     * @return AssetFilterManager
     */
    public function getAssetFilterManager()
    {
        return $this->filterManager;
    }

    /**
     * Set the AssetCacheManager.
     *
     * @param AssetCacheManager $cacheManager
     */
    public function setAssetCacheManager(AssetCacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * Get the AssetCacheManager
     *
     * @return AssetCacheManager
     */
    public function getAssetCacheManager()
    {
        return $this->cacheManager;
    }
}
