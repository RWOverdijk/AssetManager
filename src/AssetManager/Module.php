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
use Zend\Console\Adapter\AdapterInterface;
use Zend\Psr7Bridge\Psr7Response;
use Zend\Psr7Bridge\Psr7ServerRequest;
use Zend\Stdlib\RequestInterface;

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

        $psr7Request = $this->getPSR7Request($request);

        if (!$assetManager->resolvesToAsset($psr7Request)) {
            return;
        }
        
        $response = $assetManager->setAssetOnResponse(Psr7Response::fromZend($response));
        
        return Psr7Response::toZend($response);
    }
    
    protected function getPSR7Request(RequestInterface $request)
    {
        $uri        = $request->getUri();
        $fullPath   = $uri->getPath();
        
        /* Type cast string added for php 5.6 */
        $path       = (string) substr($fullPath, strlen($request->getBasePath()) + 1);

        $psr7Request = Psr7ServerRequest::fromZend($request);
        $assetRequestUri = $psr7Request->getUri()->withPath($path);
        return $psr7Request->withUri($assetRequestUri);
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
