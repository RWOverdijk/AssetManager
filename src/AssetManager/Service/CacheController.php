<?php

namespace AssetManager\Service;

use Zend\Http\Headers;

/**
 * Factory class for AssetManagerService
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class CacheController
{
    /**
     * @var array
     */
    protected $config = array();

    /**
     * @var bool|null
     */
    protected $etag = null;

    public function __construct($config = array())
    {
        if (isset($config['cache_control'])) {
            $this->setConfig($config['cache_control']);
        }
    }

    /**
     * Add cache control headers to the response
     *
     * @param \Zend\Http\Headers $headers
     */
    public function addHeaders(Headers $headers)
    {
        $headers->addHeaderLine('Cache-Control', 'public, max-age=' . $this->getLifetime());
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return array|null
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param bool $bool
     */
    public function setEtag($bool)
    {
        $this->etag = $bool;
    }

    /**
     * @return bool|null
     */
    public function getEtag()
    {
        return $this->etag;
    }

    /**
     * @return int
     */
    public function getLifetime()
    {
        return 5*60;
    }
}
