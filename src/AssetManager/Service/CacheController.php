<?php

namespace AssetManager\Service;

use Assetic\Asset\AssetInterface;
use Zend\Http\Headers;

/**
 * Cache controller service
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
    public function addHeaders(Headers $headers, AssetInterface $asset)
    {
        if ($this->config !== array())
        {
            $lastModified = date("D,d M Y H:i:s T", $asset->getLastModified());
            $headers->addHeaderLine('Cache-Control', 'max-age=' . $this->getLifetime().', public');
            $headers->addHeaderLine('Expires', date("D,d M Y H:i:s T", time() + $this->getLifetime()));
            $headers->addHeaderLine('Last-Modified', $lastModified);
            $headers->addHeaderLine('Pragma', '');
        }
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
        if (isset($this->config['lifetime'])) {
            $lifetime = $this->config['lifetime'];
        }
        return 5*60;
    }
}
