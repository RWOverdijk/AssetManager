<?php

namespace AssetManager\Service;

use Assetic\Asset\AssetInterface;
use AssetManager\Resolver\ResolverInterface;
use AssetManager\Exception;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;
use Zend\Http\PhpEnvironment\Request;

/**
 * @category    AssetManager
 * @package     AssetManager
 * @todo        Add filtering and caching.
 */
class AssetManager
{
    /**
     * @var ResolverInterface
     */
    protected $resolver;

    /**
     * Null when not resolved, false when not found, string when succesfully resolved.
     * @var mixed $resolved
     */
    protected $resolved;

    /**
     * @var string The asset loaded from file(s).
     */
    protected $asset;

    /**
    * @var string The mime type of the asset loaded.
    */
    protected $mimeType;

    /**
     * @param ResolverInterface $resolver
     */
    public function __construct($resolver)
    {
        $this->setResolver($resolver);
    }

    /**
     * Check if the request resolves to an asset.
     *
     * @param    RequestInterface $request
     * @return   boolean
     */
    public function resolvesToAsset(RequestInterface $request)
    {
        if (null === $this->resolved) {
            $this->resolved = $this->resolve($request);
        }

        return (bool)$this->resolved;
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
        $asset = $this->getAsset();

        if (!$asset instanceof AssetInterface) {
            throw new Exception\RuntimeException(
                'Unable to set asset on response. Request has not been resolved to an asset.'
            );
        }

        if (function_exists('mb_strlen')) {
            $fileSize = mb_strlen($asset, '8bit');
        } else {
            $fileSize = strlen($asset);
        }

        $response->getHeaders()
                 ->addHeaderLine('Content-Transfer-Encoding', 'binary')
                 ->addHeaderLine('Content-Type', $this->mimeType)
                 ->addHeaderLine('Content-Length', $fileSize);

        $response->setContent($asset);

        return $response;
    }

    /**
    * Get the asset value.
    *
    * @return string The asset.
    */
    public function getAsset()
    {
        if (null === $this->asset) {
            $this->loadAsset();
        }

        return $this->asset;
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
        $asset      = $this->getResolver()->resolve($path);

        if (!$asset instanceof AssetInterface) {
            return false;
        }

        return $asset;
    }
}
