<?php

namespace AssetManagerTest;

use PHPUnit_Framework_TestCase;
use Zend\View\Renderer\PhpRenderer as View;
use Assetic\Asset\StringAsset;

class HeadScriptTest extends PHPUnit_Framework_TestCase
{
    public function testRewriteHeadScriptContentWithMagicEtag()
    {
        $view     = new View();
        $resolver = $this->getMock('AssetManager\Resolver\AggregateResolver', array('resolve'));
        $sl       = $this->getMock('Zend\ServiceManager\ServiceManager', array('getServiceLocator'));
        $cacheController  = $this->getMock('AssetManager\Service\CacheController', array('calculateEtag'));
        $helper   = new \AssetManager\Helper\HeadScript($sl);
        $helper->setView($view);

        $resolver->expects($this->once())->method('resolve')->will($this->returnValue(new StringAsset('foo')));
        $cacheController->expects($this->once())->method('calculateEtag')->will($this->returnValue('a-b-c'));

        $helper->prependFile('js/bootstrap.js');

        $sm = new \Zend\ServiceManager\ServiceManager();
        $sm->setService('AssetManager\Service\AggregateResolver', $resolver);
        $sm->setService('AssetManager\Service\CacheController', $cacheController);

        $sl->expects($this->once())->method('getServiceLocator')->will($this->returnValue($sm));

        $foo = $helper->toString();
        $this->assertSame(
            '<script type="text/javascript" src="js/bootstrap.js;AMa-b-c"></script>',
            $foo
        );
        $this->assertSame($sl, $helper->getServiceLocator());
    }
}
