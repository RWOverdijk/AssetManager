<?php

namespace AssetManager\Service;


use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Http\PhpEnvironment\Request;

/**
 * Factory class for AssetManagerService
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class AssetManagerServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return AssetManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $assetManager   = new AssetManager($serviceLocator->get('AssetManager\Service\ResolverInterface'));

        $request = $serviceLocator->get('request');

        if ($request instanceof Request) {
            /* @var $request Request */
            $assetManager->setBasePath($request->getBasePath());
        }

        return $assetManager;
    }
}
