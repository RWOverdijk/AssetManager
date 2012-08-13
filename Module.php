<?php

namespace AssetManager;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\ModuleManager;
use Zend\ModuleManager\ModuleEvent;

/**
 * Module class
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class Module implements AutoloaderProviderInterface
{
    /**
     * Init method for module init.
     *
     * @param ModuleManager $moduleManager
     */
    public function init(ModuleManager $moduleManager)
    {
        $moduleManager->getEventManager()->attach('loadModules.post', array($this, 'modulesLoaded'));
    }

    /**
     * Callback for event loadModules.post
     *
     * @param ModuleEvent $event
     */
    public function modulesLoaded(ModuleEvent $event)
    {
        // $event->getParam('ServiceManager')->get('asset_manager')
    }

    /**
     * Get the configuration for the autoloader
     *
     * @return array config array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/' , __NAMESPACE__),
                ),
            ),
        );
    }

    /**
     * Get the configuration from this module.
     *
     * @return array config array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}
