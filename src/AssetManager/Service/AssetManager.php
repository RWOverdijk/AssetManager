<?php

namespace AssetManager\Service;

use Zend\ServiceManager\ServiceLocatorInterface;

class AssetManager
{

    protected $options;
    protected $serviceLocator;
    protected $basePath;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
        return $this;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}