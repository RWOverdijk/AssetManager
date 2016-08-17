<?php

namespace AssetManager\MiddleWare;

use AssetManager\Service\AssetManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AssetManagerMiddleware
{
    protected $assetManager;

    public function __construct(AssetManager $assetManager)
    {
        $this->assetManager = $assetManager;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {

        if (!$this->assetManager->resolvesToAsset($request)) {
            if (!$next) {
                return $response;
            }

            return $next($request, $response);
        }

        return $this->assetManager->setAssetOnResponse($response);
    }
}
