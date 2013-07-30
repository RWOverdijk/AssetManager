<?php
namespace AssetManager\View\Helper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;

class HeadScript extends \Zend\View\Helper\HeadScript implements
    ServiceLocatorAwareInterface
{
    use \Zend\ServiceManager\ServiceLocatorAwareTrait;
    use \AssetManager\ServiceManager\ServiceTrait;

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator->getServiceLocator();
    }

    /**
     * {@inheritdoc}
     */
    public function toString($indent = null)
    {
        // When there is only one resource dont do anything
        if (count($this) <= 1) {
            return parent::toString($indent);
        }

        $this->getContainer()->ksort();

        $collection = array();
        $hashString = '';
        $itemsToKeep     = array();

        foreach ($this as $item) {
            // Not all script appends are files, they can be just plain sources..
            if (!isset($item->attributes['src'])) {
                $itemsToKeep[] = $item;
                continue;
            }
            $collection[] = ltrim($item->attributes['src'], '/\\');
            $hashString  .= ltrim($item->attributes['src'], '/\\');
        }

        $collectionIdentifier = md5($hashString) . '.js';

        $result = $this->getAssetManagerDynamicCollectionCacheService()
                       ->addDynamicCollection($collectionIdentifier, $collection);

        // When the dynamic collection was not added, dont do anything
        if (false === $result) {
            return parent::toString($indent);
        }

        $this->deleteContainer();

        $item = new \stdClass();
        $item->type                  = 'text/javascript';
        $item->attributes            = array('src' => '/' . $collectionIdentifier);
        $item->media                 = 'screen';
        $item->conditionalStylesheet = false;

        $this->append($item);
        foreach ($itemsToKeep as $itemToKeep) {
            $this->append($itemToKeep);
        }

        return parent::toString($indent);
    }
}
