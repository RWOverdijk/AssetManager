<?php
namespace AssetManager\View\Helper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;

class HeadLink extends \Zend\View\Helper\HeadLink implements
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

        foreach ($this as $item) {
            $collection[] = ltrim($item->href, '/\\');
            $hashString  .= ltrim($item->href, '/\\');
        }

        $collectionIdentifier = md5($hashString) . '.css';

        $result = $this->getAssetManagerDynamicCollectionCacheService()
                       ->addDynamicCollection($collectionIdentifier, $collection);

        // When the dynamic collection was not added, dont do anything
        if (false === $result) {
            return parent::toString($indent);
        }

        $this->deleteContainer();

        $item = new \stdClass();
        $item->rel                   = 'stylesheet';
        $item->type                  = 'text/css';
        $item->href                  = '/' . $collectionIdentifier;
        $item->media                 = 'screen';
        $item->conditionalStylesheet = false;

        $this->append($item);

        return parent::toString($indent);
    }

}
