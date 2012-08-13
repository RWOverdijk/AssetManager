<?php

namespace AssetManager\Service;

use Zend\ServiceManager\ServiceLocatorInterface,
    \finfo,
    \SplFileInfo;

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

    public function send($file)
    {
        $finfo      = new finfo(FILEINFO_MIME);
        $mimeType   = $finfo->file($file);
        $fileinfo   = new SplFileInfo($file);
        $file       = $fileinfo->openFile('rb');

        header("Content-Type: $mimeType");
        header("Content-Length: " . $file->getSize());

        $file->fpassthru();
        exit;
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