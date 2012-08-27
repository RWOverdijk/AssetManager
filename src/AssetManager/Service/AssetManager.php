<?php

namespace AssetManager\Service;

use AssetManager\Resolver\ResolverInterface;
use AssetManager\Exception;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;
use Zend\Http\PhpEnvironment\Request;
use finfo;

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

    public function setResolver(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

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

        if (!is_string($asset)) {
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
    * Load the resolved asset.
    */
    protected function loadAsset()
    {
        if (is_string($this->resolved)) {
            $this->addAsset($this->resolved);
        }

        if (is_array($this->resolved)) {
            $this->loadAssets($this->resolved);
        }
    }

    /**
    * (recursively) load assets.
    *
    * @param array $assets The assets to load.
    * @throws Exception\RuntimeException
    */
    protected function loadAssets(array $assets)
    {
        foreach ($assets as $asset) {
            if (!is_string($asset)) {
                throw new Exception\RuntimeException(
                    'Asset should be of type string. got ' . gettype($asset)
                );
            }

            if (null === ($res = $this->getResolver()->resolve($asset))) {
                throw new Exception\RuntimeException("Asset '$asset' could not be found.");
            }

            if (is_array($res)) {
                $this->loadAssets($res);
                continue;
            }

            $this->addAsset($res);
        }
    }

    /**
    * Add an asset to the asset string.
    *
    * @param string $file
    * @throws Exception\RuntimeException
    */
    protected function addAsset($file)
    {
        if (!file_exists($file)) {
            throw new Exception\RuntimeException(
                "File '$file' could not be found."
            );
        }

        $finfo      = new finfo(FILEINFO_MIME);
        $mimeType   = $finfo->file($file);

        if ($this->mimeType !== null && $this->mimeType !== $mimeType) {
            throw new Exception\RuntimeException(
                'Trying to combine files with different mimetypes.'
            );
        }

        $this->mimeType = $mimeType;

        $this->asset .= file_get_contents($file);
    }

    /**
     * Resolve the request to a file.
     *
     * @param RequestInterface $request
     *
     * @return mixed false when not found, string when succesfully resolved.
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

        if (null === ($file = $this->getResolver()->resolve($path))) {
            return false;
        }

        return $file;
    }
}
