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
     * {@inheritDoc}
     */
    public function onBootstrap(EventInterface $e)
    {
        /* @var $application \Zend\Mvc\ApplicationInterface */
        $application = $e->getTarget();
        /* @var $assetManager \AssetManager\Service\AssetManager */
        $assetManager = $application->getServiceManager()->get(__NAMESPACE__ . '\Service\AssetManager');

        if ($assetManager->serveAsset($application->getRequest())) {
            // enforcing application stop - does still allow EVENT_FINISH
            $evm = $application->getEventManager();
            $evm->clearListeners(MvcEvent::EVENT_DISPATCH);
            $evm->clearListeners(MvcEvent::EVENT_DISPATCH_ERROR);
            $evm->clearListeners(MvcEvent::EVENT_ROUTE);
            $evm->clearListeners(MvcEvent::EVENT_RENDER);

            // @todo also detach from shared event manager?
            // @todo this could also be avoided if we move the asset manager to a controller and use the standard MVC
        }
    }
}
