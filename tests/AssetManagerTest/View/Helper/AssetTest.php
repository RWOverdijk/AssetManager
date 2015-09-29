<?php
namespace AssetManagerTest\View\Helper;

use PHPUnit_Framework_TestCase as TestCase;
use AssetManager\View\Helper\Asset;
use Zend\ServiceManager\ServiceManager;

class AssetTest extends TestCase
{
    public function testInvoke()
    {
        /* TODO need help
        $serviceManager = new ServiceManager();
        $filename = 'js/js.js';
        $helper = new Asset($serviceManager);

        $this->assertContains('?_=', $helper->__invoke($filename));
        */
    }
}
