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
     * @param array
     */
    protected $etagStorage = array();

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
        if ($this->config !== array()) {
            $lastModified = date("D,d M Y H:i:s T", $asset->getLastModified());

            $lifetime = $this->getLifetime();
            $headers->addHeaderLine('Cache-Control', 'max-age=' . $lifetime .', public');
            $headers->addHeaderLine('Expires', date("D,d M Y H:i:s T", time() + $lifetime));

            $headers->addHeaderLine('Last-Modified', $lastModified);
            $headers->addHeaderLine('Pragma', '');

            if ($this->hasEtag()) {
                $headers->addHeaderLine('ETag', $this->calculateEtag($asset));
            }
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
     * @return bool|null
     */
    public function hasEtag()
    {
        if (isset($this->config['etag'])) {
            return $this->config['etag'];
        }

        return false;
    }

    /**
     * @return int
     */
    public function getLifetime()
    {
        if (isset($this->config['lifetime'])) {
            $lifetime = $this->config['lifetime'];
        }

        $minute = 60;
        $hour = 60*60;
        $day = 24*60*60;

        preg_match("/(\d+)(\w)/", $lifetime, $match);

        if (count($match) === 0 || count($match) < 3) {
            throw new \AssetManager\Exception\InvalidArgumentException("Invalid format");
        }

        if ($match[2] == 'd') {
            return $match[1]*$day;
        } elseif ($match[2] == 'h') {
            return $match[1]*$hour;
        } elseif ($match[2] == 'm') {
            return $match[1]*$minute;
        } else {
            throw new \AssetManager\Exception\InvalidArgumentException("Valid formatters are d,h,m");
        }
    }

    public function calculateEtag(AssetInterface $asset)
    {
        $objectHash = spl_object_hash($asset);

        if (isset($this->etagStorage[$objectHash])) {
            return $this->etagStorage[$objectHash];
        }

        $mtime = $asset->getLastModified();
        $size = null;

        $assetContents = $asset->dump();

        // @codeCoverageIgnoreStart
        if (function_exists('mb_strlen')) {
            $size = mb_strlen($assetContents, '8bit');
        } else {
            $size = strlen($assetContents);
        }
        // @codeCoverageIgnoreEnd

        $etag = sprintf('%x-%x-%016x', 1, $size, $mtime);
        $this->etagStorage[$objectHash] = $etag;

        return $etag;
    }
}
