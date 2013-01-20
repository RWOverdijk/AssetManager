<?php
namespace AssetManager\Helper;

use Zend\View\Helper\HeadScript as StandardHeadScript;
use Zend\View\Helper\Placeholder\Container\AbstractContainer;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class HeadScript extends StandardHeadScript
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
        $container = $this->getContainer();

        /** @var $aggregateResolver \AssetManager\Resolver\AggregateResolver */
        $mainLocator = $this->sl->getServiceLocator();
        $aggregateResolver = $mainLocator->get('AssetManager\Service\AggregateResolver');
        $cacheController = $mainLocator->get('AssetManager\Service\CacheController');

        if (!$cacheController->hasMagicEtag()) {
            return $value;
        }

        foreach($container as $element)
        {
            $source = $element->attributes["src"];
            $asset = $aggregateResolver->resolve($source);
            $value = str_replace($source, $source.';ETag'.$cacheController->calculateEtag($asset), $value);
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
