<?php
namespace AssetManager\Helper;

use Zend\View\Helper\HeadLink as StandardHeadLink;
use Zend\View\Helper\Placeholder\Container\AbstractContainer;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class HeadLink extends StandardHeadLink implements ServiceLocatorAwareInterface
{
    protected $sl = null;

    public function __invoke(array $attributes = null, $placement = AbstractContainer::APPEND)
    {
        $value = parent::__invoke($attributes, $placement);
        /** @var $pathStackResolver \AssetManager\Resolver\PathStackResolver */
        $pathStackResolver = $this->sl->getServiceLocator()->get('AssetManager\Resolver\PathStackResolver');
        $pathStackResolver->setMimeResolver($this->sl->getServiceLocator()->get('mime_resolver'));

        $container = $this->getContainer();
        foreach($container as $element)
        {
            $timestamp = $pathStackResolver->resolve($element->href)->getLastModified();
            $value = str_replace($element->href, $element->href.';mtime'.$timestamp, $value);
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
