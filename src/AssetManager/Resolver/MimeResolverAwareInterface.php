<?php

namespace AssetManager\Resolver;

use AssetManager\Service\MimeResolver;

interface MimeResolverAwareInterface
{
    /**
     * Set the MimeResolver.
     *
     * @param MimeResolver $resolver
     */
    public function setMimeResolver(MimeResolver $resolver);

    /**
     * Get the MimeResolver
     *
     * @return MimeResolver
     */
    public function getMimeResolver();
}
