<?php

namespace AssetManager\Service;

class AssetCacheBustingManager
{
    protected $config = array();

    public function __construct($config = array())
    {
        $this->config = $config;
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function isEnabled()
    {
        if (isset($this->config['enabled']) && $this->config['enabled']) {
            return true;
        }

        return false;
    }

    public function getOverrideHeadHelper()
    {
        if (isset($this->config['override_head_helper']) && $this->config['override_head_helper']) {
            return true;
        }

        return false;
    }
}
