<?php

namespace AssetManager\Helper;

use AssetManager\Helper\HeadScript;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\HeadScript as StandardHeadScript;

/**
 * Factory class for HeadLink
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class HeadScriptServiceFactory implements FactoryInterface
{

    /**
     * {@inheritDoc}
     *
     * @return \Zend\View\Helper\HeadLink
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config         = $serviceLocator->getServiceLocator()->get('Config');
        $config         = isset($config['asset_manager']) ? $config['asset_manager'] : array();

        if (
            isset($config['cache_busting']['override_head_helper'])
            && $config['cache_busting']['override_head_helper']
        ) {

            return new HeadScript($serviceLocator);
        }

        return new StandardHeadScript();
    }
}
