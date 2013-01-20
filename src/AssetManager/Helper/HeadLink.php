<?php
namespace AssetManager\Helper;

use Zend\View\Helper\HeadLink as StandardHeadLink;
use Zend\View\Helper\Placeholder\Container\AbstractContainer;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class HeadLink extends StandardHeadLink
{
    protected $sl = null;

    public function __construct(ServiceLocatorInterface $sl)
    {
        parent::__construct();
        $this->sl = $sl;
    }

    public function toString($indent = null)
    {
        $value = parent::toString($indent);
        /** @var $aggregateResolver \AssetManager\Resolver\AggregateResolver */
        $mainLocator = $this->sl->getServiceLocator();
        $aggregateResolver = $mainLocator->get('AssetManager\Service\AggregateResolver');
        $cacheController = $mainLocator->get('AssetManager\Service\CacheController');

        if (!$cacheController->hasMagicEtag()) {
            return $value;
        }

        $container = $this->getContainer();
        foreach($container as $element)
        {
            $asset = $aggregateResolver->resolve($element->href);
            $etag = $cacheController->calculateEtag($asset);
            $value = str_replace($element->href, $element->href.';ETag'.$etag, $value);
        }

        return $value;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $sl)
    {
        $this->sl = $sl;
    }

    public function getServiceLocator()
    {
        return $this->sl;
    }
}
