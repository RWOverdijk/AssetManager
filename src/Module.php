<?php

namespace AssetManager;

use AssetManager\Core\Service\AssetManager;
use Zend\Console\Adapter\AdapterInterface;
use Zend\EventManager\EventInterface;
use Zend\Loader\AutoloaderFactory;
use Zend\Loader\StandardAutoloader;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Mvc\MvcEvent;
use Zend\Psr7Bridge\Psr7Response;
use Zend\Psr7Bridge\Psr7ServerRequest;

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
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * Callback method for dispatch and dispatch.error events.
     *
     * @param MvcEvent $event
     * @return \Zend\Http\Response|null
     */
    public function onDispatch(MvcEvent $event)
    {
        /* @var $zendResponse \Zend\Http\Response */
        $zendResponse = $event->getResponse();
        if (!method_exists($zendResponse, 'getStatusCode') || $zendResponse->getStatusCode() !== 404) {
            return null;
        }

        $response       = Psr7Response::fromZend($zendResponse);
        $request        = Psr7ServerRequest::fromZend($event->getRequest());
        $serviceManager = $event->getApplication()->getServiceManager();

        /** @var AssetManager $assetManager */
        $assetManager   = $serviceManager->get(AssetManager::class);

        if (!$assetManager->resolvesToAsset($request)) {
            return null;
        }

        $zendResponse = Psr7Response::toZend($assetManager->setAssetOnResponse($response));
        $zendResponse->setStatusCode(200);

        return $zendResponse;
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
