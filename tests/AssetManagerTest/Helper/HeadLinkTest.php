<?php

namespace AssetManagerTest;

use PHPUnit_Framework_TestCase;
use Zend\View\Renderer\PhpRenderer as View;
use Assetic\Asset\StringAsset;

class HeadLinkTest extends PHPUnit_Framework_TestCase
{
    public function testRewriteHeadlinkContentWithCacheBusting()
    {
        $view     = new View();
        $resolver = $this->getMock('AssetManager\Resolver\AggregateResolver', array('resolve'));
        $sl       = $this->getMock('Zend\ServiceManager\ServiceManager', array('getServiceLocator'));
        $cacheController  = $this->getMock('AssetManager\Service\CacheController', array('calculateEtag'));
        $helper   = new \AssetManager\Helper\HeadLink($sl);
        $helper->setView($view);

        $resolver->expects($this->once())->method('resolve')->will($this->returnValue(new StringAsset('foo')));
        $cacheController->expects($this->once())->method('calculateEtag')->will($this->returnValue('a-b-c'));

        $helper->appendStylesheet('/css/bootstrap-white.css');

        $sm = new \Zend\ServiceManager\ServiceManager();
        $sm->setService('AssetManager\Service\AggregateResolver', $resolver);
        $sm->setService('AssetManager\Service\CacheController', $cacheController);

        $sl->expects($this->once())->method('getServiceLocator')->will($this->returnValue($sm));

        $foo = $helper->toString();
        $this->assertSame(
            '<link href="/css/bootstrap-white.css;AMa-b-c" media="screen" rel="stylesheet" type="text/css">',
            $foo
        );
        $this->assertSame($sl, $helper->getServiceLocator());
    }
}
