<?php

namespace AssetManager\Resolver;

interface ResolverInterface
{
    /**
     * Resolve an Asset
     *
     * @param   string  $path   The path to resolve.
     *
     * @return  \Assetic\Asset\AssetInterface|null Asset instance when found, null when not.
     */
    public function resolve($path);
}
