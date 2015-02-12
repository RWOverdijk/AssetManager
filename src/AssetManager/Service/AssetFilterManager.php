<?php

namespace AssetManager\Service;

use Assetic\Asset\AssetInterface;
use Assetic\Filter\FilterInterface;
use AssetManager\Exception;
use AssetManager\Resolver\MimeResolverAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class AssetFilterManager implements ServiceLocatorAwareInterface, MimeResolverAwareInterface
{
    /**
     * @var array Filter configuration.
     */
    protected $config;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var MimeResolver
     */
    protected $mimeResolver;
    
    /**
     * @var FilterInterface[] Filters already instantiated
     */
    protected $filterInstances = array();
    
    /**
     * Construct the AssetFilterManager
     *
     * @param   array $config
     * @return  AssetFilterManager
     */
    public function __construct(array $config = array())
    {
        $this->setConfig($config);
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
     *
     * @throws Exception\RuntimeException on invalid filters
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
            if (is_null($filter)) {
                continue;
            }
            if (!empty($filter['filter'])) {
                $this->ensureByFilter($asset, $filter['filter']);
            } elseif (!empty($filter['service'])) {
                $this->ensureByService($asset, $filter['service']);
            } else {
                throw new Exception\RuntimeException(
                    'Invalid filter supplied. Expected Filter or Service.'
                );
            }
        }
    }

    /**
     * Ensure that the filters as service are set.
     *
     * @param   AssetInterface  $asset
     * @param   string          $service    A valid service name.
     * @throws  Exception\RuntimeException
     */
    protected function ensureByService(AssetInterface $asset, $service)
    {
        if (is_string($service)) {
            $this->ensureByFilter($asset, $this->getServiceLocator()->get($service));
        } else {
            throw new Exception\RuntimeException(
                'Unexpected service provided. Expected string or callback.'
            );
        }
    }

    /**
     * Ensure that the filters as filter are set.
     *
     * @param   AssetInterface  $asset
     * @param   mixed           $filter    Either an instance of FilterInterface or a classname.
     * @throws  Exception\RuntimeException
     */
    protected function ensureByFilter(AssetInterface $asset, $filter)
    {
        if ($filter instanceof FilterInterface) {
            $filterInstance = $filter;
            $asset->ensureFilter($filterInstance);

            return;
        }

        $filterClass = $filter;

        if (!is_subclass_of($filterClass, 'Assetic\Filter\FilterInterface', true)) {
            $filterClass .= (substr($filterClass, -6) === 'Filter') ? '' : 'Filter';
            $filterClass  = 'Assetic\Filter\\' . $filterClass;
        }

        if (!class_exists($filterClass)) {
            throw new Exception\RuntimeException(
                'No filter found for ' . $filter
            );
        }

        if (!isset($this->filterInstances[$filterClass])) {
            $this->filterInstances[$filterClass] = new $filterClass();
        }

        $filterInstance = $this->filterInstances[$filterClass];

        $asset->ensureFilter($filterInstance);
    }

    /**
     * {@inheritDoc}
     */
    public function getMimeResolver()
    {
        return $this->mimeResolver;
    }

    /**
     * {@inheritDoc}
     */
    public function setMimeResolver(MimeResolver $resolver)
    {
        $this->mimeResolver = $resolver;
    }

    /**
     * {@inheritDoc}
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * {@inheritDoc}
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }
}
