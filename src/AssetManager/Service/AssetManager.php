<?php

namespace AssetManager\Service;

class AssetManager
{

    protected $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }
}