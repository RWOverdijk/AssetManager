<?php
namespace AssetManager\Helper;

use Zend\View\Helper\HeadLink as StandardHeadLink;
use Zend\View\Helper\Placeholder\Container\AbstractContainer;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class HeadLink extends StandardHeadLink implements ServiceLocatorAwareInterface
{
    protected $sl = null;

    public function toString($indent = null)
    {
        $value = parent::toString($indent);
        /** @var $pathStackResolver \AssetManager\Resolver\PathStackResolver */
        $pathStackResolver = $this->sl->getServiceLocator()->get('AssetManager\Service\AggregateResolver');

        $container = $this->getContainer();

        foreach($container as $element)
        {
            #$timestamp = $pathStackResolver->resolve($element->href)->getLastModified();
            $value = str_replace($element->href, $element->href.'', $value);
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
