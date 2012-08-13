<?php

namespace AssetManager\Service;

use Zend\ServiceManager\ServiceLocatorInterface;
use \finfo;
use \SplFileInfo;

/**
 * @category    AssetManager
 * @package     AssetManager
 */
class AssetManager
{
    /**
     * @var Array AssetManager options
     */
    protected $options;

    /**
     * @var ServiceLocatorInterface ServiceLocator
     */
    protected $serviceLocator;

    /**
     * @var string The asset basePath
     */
    protected $basePath;

    /**
     * Constructor
     *
     * Instantiate the AssetManager service
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * Get the basePath
     *
     * @return string|null
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Set the basePath
     *
     * @param string $basePath
     * @return AssetManager
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
        return $this;
    }

    /**
     * Output any asset.
     *
     * @param string $file /Path/To/File for output
     */
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

    /**
     * Set the serviceLocator
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return AssetManager
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * Get the serviceLocator
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
