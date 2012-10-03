<?php

namespace AssetManager\Service;

use Assetic\Filter\FilterInterface;
use Assetic\Asset\AssetInterface;
use AssetManager\Exception;
use AssetManager\Resolver\MimeResolverAwareInterface;
use AssetManager\Service\MimeResolver;

class AssetFilterManager implements MimeResolverAwareInterface
{
    /**
     * @var MimeResolver The mime resolver.
     */
    protected $mimeResolver;

    /**
     * @var array Filter configuration.
     */
    protected $config;

    /**
     * Construct the AssetFilterManager
     *
     * @param   array $config
     * @return  AssetFilterManager
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get the filter configuration.
     *
     * @return  array
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     * Set the filter configuration.
     *
     * @param array $config
     */
    protected function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * See if there are filters for the asset, and if so, set them.
     *
     * @param   string          $path
     * @param   AssetInterface  $asset
     */
    public function setFilters($path, AssetInterface $asset)
    {
        $config = $this->getConfig();

        if (!empty($config[$path])) {
            $filters = $config[$path];
        } elseif (!empty($config[$asset->mimetype])) {
            $filters = $config[$asset->mimetype];
        } else {
            $extension = $this->getMimeResolver()->getExtension($asset->mimetype);
            if (!empty($config[$extension])) {
                $filters = $config[$extension];
            } else {
                return;
            }
        }

        foreach ($filters as $filter) {

            if ($filter['filter'] instanceof Filter\FilterInterface) {
                $filterInstance = $filter['filter'];
                $asset->ensureFilter($filterInstance);

                continue;
            }

            $filterClass = $filter['filter'];

            if (!is_subclass_of($filterClass, 'Assetic\Filter\FilterInterface', true)) {
                $filterClass .= (substr($filterClass, -6) === 'Filter') ? '' : 'Filter';
                $filterClass  = 'Assetic\Filter\\' . $filterClass;
            }

            if (!class_exists($filterClass)) {
                throw new Exception\RuntimeException(
                    'No filter found for ' . $filter['filter']
                );
            }

            $filterInstance = new $filterClass;

            $asset->ensureFilter($filterInstance);
        }
    }

    /**
     * Set the mime resolver
     *
     * @param MimeResolver $resolver
     */
    public function setMimeResolver(MimeResolver $resolver)
    {
        $this->mimeResolver = $resolver;
    }

    /**
     * Get the mime resolver
     *
     * @return MimeResolver
     */
    public function getMimeResolver()
    {
        return $this->mimeResolver;
    }
}
