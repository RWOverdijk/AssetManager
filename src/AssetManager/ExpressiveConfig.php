<?php

namespace AssetManager;

class ExpressiveConfig
{
    public function __invoke()
    {
        return require __DIR__.'/../../config/expressive.config.php';
    }
}
