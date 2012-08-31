<?php

namespace AssetManager\Resolver;

interface ResolverInterface
{
    /**
     * Resolve an Asset
     *
     * @param  string                   $path
     * @return ResolverInterface|null   the absolute path to the file
     */
    public function resolve($path);
}
