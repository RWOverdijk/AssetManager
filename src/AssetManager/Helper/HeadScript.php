<?php
namespace AssetManager\Helper;

use Zend\View\Helper\HeadScript as StandardHeadScript;
use Zend\View\Helper\Placeholder\Container\AbstractContainer;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class HeadScript extends StandardHeadScript implements ServiceLocatorAwareInterface
{
    protected $sl = null;

    public function toString($indent = null)
    {
        $value = parent::toString($indent);
        $pathStackResolver = $this->sl->getServiceLocator()->get('AssetManager\Service\AggregateResolver');
        $container = $this->getContainer();

        foreach($container as $element)
        {
            $source = $element->attributes["src"];
            $timestamp = $pathStackResolver->resolve($source)->getLastModified();
            $value = str_replace($source, $source.';mtime'.$timestamp, $value);
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
