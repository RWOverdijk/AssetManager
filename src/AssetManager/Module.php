<?php

namespace AssetManager;

use Zend\Loader\StandardAutoloader;
use Zend\Loader\AutoloaderFactory;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\Mvc\MvcEvent;

/**
 * Module class
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class Module implements
    AutoloaderProviderInterface,
    ConfigProviderInterface,
    BootstrapListenerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getAutoloaderConfig()
    {
        return array(
            AutoloaderFactory::STANDARD_AUTOLOADER => array(
                StandardAutoloader::LOAD_NS => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * Callback method for dispatch and dispatch.error events.
     *
     * @param MvcEvent $event
     */
    public function onDispatch(MvcEvent $event)
    {
        $response = $event->getResponse();
        if (!method_exists($response, 'getStatusCode') || $response->getStatusCode() !== 404) {
            return;
        }

        $request        = $event->getRequest();
        /** @var $headers  \Zend\Http\Headers */
        $headers        = $request->getHeaders();
        $uri            = $request->getUri();
        $pos            = strpos($uri->getPath(), ';AM');

        if (
            $pos !== false
            && $headers->has('If-None-Match')
        ) {
            $response->setStatusCode(304);
            $responseHeaders = $response->getHeaders();
            $responseHeaders->addHeaderLine('Cache-Control', '');
            return $response;
        }

        if ($pos !== false) {
            $uri->setPath(substr($uri->getPath(), 0, $pos));
        }

        $serviceManager = $event->getApplication()->getServiceManager();
        $assetManager   = $serviceManager->get(__NAMESPACE__ . '\Service\AssetManager');

        if (!$assetManager->resolvesToAsset($request)) {
            return;
        }

        if ($headers->has('If-Modified-Since')) {
            $asset = $assetManager->resolve($request);
            $lastModified = $asset->getLastModified();
            $modifiedSince = strtotime($headers->get('If-Modified-Since')->getDate());

            if ($lastModified <= $modifiedSince) {
                $response->setStatusCode(304);
                $responseHeaders = $response->getHeaders();
                $responseHeaders->addHeaderLine('Cache-Control', '');
                return $response;
            }
        }

        if ($headers->has('If-None-Match')) {
            $cacheController = $assetManager->getCacheController();
            $asset = $assetManager->resolve($request);

            $assetManager->getAssetFilterManager()->setFilters($uri, $asset);
            $etag = $cacheController->calculateEtag($asset);

            $match = $headers->get('If-None-Match')->getFieldValue();

            if ($etag == $match) {
                $response->setStatusCode(304);
                $responseHeaders = $response->getHeaders();
                $responseHeaders->addHeaderLine('Cache-Control', '');
                return $response;
            }
        }

        $response->setStatusCode(200);

        return $assetManager->setAssetOnResponse($response);
    }

    /**
     * {@inheritDoc}
     */
    public function onBootstrap(EventInterface $event)
    {
        // Attach for dispatch, and dispatch.error (with low priority to make sure statusCode gets set)
        $eventManager = $event->getTarget()->getEventManager();
        $callback     = array($this, 'onDispatch');
        $priority     = -9999999;
        $eventManager->attach(MvcEvent::EVENT_DISPATCH,       $callback, $priority);
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, $callback, $priority);
    }
}
