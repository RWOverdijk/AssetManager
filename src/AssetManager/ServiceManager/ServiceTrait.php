<?php
namespace AssetManager\ServiceManager;

/**
 * Collection of get methods for retrieving services
 *
 * @category    AssetManager
 * @package     ServiceManager
 */
trait ServiceTrait
{
    abstract public function getServiceLocator();

    /**
     * @return \AssetManager\Service\AssetManager
     */
    public function getAssetManagerService()
    {
        return $this->getServiceLocator()->get(
            'AssetManager\Service\AssetManager'
        );
    }

    /**
     * @return \AssetManager\Service\AssetFilterManager
     */
    public function getAssetManagerAssetFilterManagerService()
    {
        return $this->getServiceLocator()->get(
            'AssetManager\Service\AssetFilterManager'
        );
    }

    /**
     * @return \AssetManager\Service\AssetCacheManager
     */
    public function getAssetManagerAssetCacheManagerService()
    {
        return $this->getServiceLocator()->get(
            'AssetManager\Service\AssetCacheManager'
        );
    }

    /**
     * @return \AssetManager\Service\AggregateResolver
     */
    public function getAssetManagerAggregateResolverService()
    {
        return $this->getServiceLocator()->get(
            'AssetManager\Service\AggregateResolver'
        );
    }

    /**
     * @return \AssetManager\Service\MapResolver
     */
    public function getAssetManagerMapResolverService()
    {
        return $this->getServiceLocator()->get(
            'AssetManager\Service\MapResolver'
        );
    }

    /**
     * @return \AssetManager\Service\PathStackResolver
     */
    public function getAssetManagerPathStackResolverService()
    {
        return $this->getServiceLocator()->get(
            'AssetManager\Service\PathStackResolver'
        );
    }

    /**
     * @return \AssetManager\Service\PrioritizedPathsResolver
     */
    public function getAssetManagerPrioritizedPathsResolverService()
    {
        return $this->getServiceLocator()->get(
            'AssetManager\Service\PrioritizedPathsResolver'
        );
    }

    /**
     * @return \AssetManager\Service\CollectionResolver
     */
    public function getAssetManagerCollectionResolverService()
    {
        return $this->getServiceLocator()->get(
            'AssetManager\Service\CollectionResolver'
        );
    }

    /**
     * @return \AssetManager\Service\DynamicCollectionCache
     */
    public function getAssetManagerDynamicCollectionCacheService()
    {
        return $this->getServiceLocator()->get(
            'AssetManager\Service\DynamicCollectionCache'
        );
    }
}
