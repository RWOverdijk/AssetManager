<?php

namespace AssetManager;

use Zend\Console\Adapter\AdapterInterface;
use Zend\EventManager\EventInterface;
use Zend\Loader\AutoloaderFactory;
use Zend\Loader\StandardAutoloader;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
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
        /* @var $response \Zend\Http\Response */
        $response = $event->getResponse();
        if (!method_exists($response, 'getStatusCode') || $response->getStatusCode() !== 404) {
            return;
        }
        $request        = $event->getRequest();
        $serviceManager = $event->getApplication()->getServiceManager();
        $assetManager   = $serviceManager->get(__NAMESPACE__ . '\Service\AssetManager');

        if (!$assetManager->resolvesToAsset($request)) {
            return;
        }

        $headers = $response->getHeaders();

        /** @var $lastModified \Zend\Http\Header\LastModified */
        $lastModified = $headers->get('Last-Modified');
        if (!$lastModified instanceof \Zend\Http\Header\LastModified) {
            $lastModified = new \Zend\Http\Header\LastModified();
            $headers->addHeader($lastModified);
        }

        /** @var $cacheControl \Zend\Http\Header\CacheControl */
        $cacheControl = $headers->get('Cache-Control');
        if (!$cacheControl instanceof \Zend\Http\Header\CacheControl) {
            $cacheControl = new \Zend\Http\Header\CacheControl();
            $cacheControl->addDirective('public');
            $headers->addHeader($cacheControl);
        }

        /** @var $modifiedSince \Zend\Http\Header\IfModifiedSince */
        $modifiedSince = $request->getHeader('If-Modified-Since');
        if ($modifiedSince instanceof \Zend\Http\Header\IfModifiedSince) {
            if ($assetManager->getAsset()->getLastModified() < $modifiedSince->date()->getTimestamp()) {
                $lastModified->setDate($modifiedSince->date());
                $response->setStatusCode($response::STATUS_CODE_304);
                $response->setContent(null);
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
        /* @var $eventManager \Zend\EventManager\EventManagerInterface */
        $eventManager = $event->getTarget()->getEventManager();
        $callback     = array($this, 'onDispatch');
        $priority     = -9999999;
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, $callback, $priority);
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, $callback, $priority);
    }

    /**
     * @param \Zend\Console\Adapter\AdapterInterface $console
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getConsoleUsage(AdapterInterface $console)
    {
        return array(
            'Warmup',
            'assetmanager warmup [--purge] [--verbose|-v]' => 'Warm AssetManager up',
            array('--purge', '(optional) forces cache flushing'),
            array('--verbose | -v', '(optional) verbose mode'),
        );
    }
}
