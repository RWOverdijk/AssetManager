<?php

namespace AssetManager\Resolver;

interface ResolverInterface
{
    /**
     * Resolve a given file name to a system path
     *
     * @param  string $path
     * @return string|null the absolute path to the file
     */
    public function resolve($path);
}
