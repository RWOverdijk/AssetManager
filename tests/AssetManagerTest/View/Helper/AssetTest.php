<?php
namespace AssetManagerTest\View\Helper;

use PHPUnit_Framework_TestCase as TestCase;
use AssetManager\View\Helper\Asset;

class AssetTest extends TestCase
{
    public function testInvoke()
    {
        $filename = '';
        $config = [];
        $helper = new Asset($config);

        $this->assertContains("?u=", $helper->__invoke($filename));
    }
}
