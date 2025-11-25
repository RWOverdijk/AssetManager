<?php

namespace AssetManager\Resolver;

use Assetic\Contracts\Asset\AssetInterface;

interface ResolverInterface
{
    /**
     * Resolve an Asset
     *
     * @param string $path The path to resolve.
     *
     * @return  AssetInterface|null Asset instance when found, null when not.
     */
    public function resolve($path);
}
