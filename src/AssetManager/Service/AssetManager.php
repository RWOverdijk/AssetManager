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
     * @param ResolverInterface $resolver
     */
    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
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
    * Set the asset on the response, including headers and content.
    *
    * @param    ResponseInterface $response
    * @return   ResponseInterface
    * @throws   Exception\RuntimeException
    */
    public function setAssetOnResponse(ResponseInterface $response)
    {
        if (!is_string(($file = $this->resolved))) {
            throw new Exception\RuntimeException(
                'Unable to set asset on response. Request has not been resolved to an asset.'
            );
        }

        $finfo      = new finfo(FILEINFO_MIME);
        $mimeType   = $finfo->file($file);
        $fileSize   = filesize($file);
        $content    = file_get_contents($file);

        $response->getHeaders()
                 ->addHeaderLine('Content-Transfer-Encoding', 'binary')
                 ->addHeaderLine('Content-Type', $mimeType)
                 ->addHeaderLine('Content-Length', $fileSize);

        $response->setContent($content);

        return $response;
    }

    /**
    * Resolve the request to a file.
    *
    * @param RequestInterface $request
    *
    * @return false when not found, string when succesfully resolved.
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

        if (null === ($file = $this->resolver->resolve($path))) {
            return false;
        }

        return $file;
    }
}
