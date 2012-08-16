<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use AssetManager\Service\AssetManager;
use Zend\ServiceManager\ServiceManager;
use Zend\Http\PhpEnvironment\Request;
use Zend\Console\Request as ConsoleRequest;
use Zend\Stdlib\ErrorHandler;

class AssetManagerTest extends PHPUnit_Framework_TestCase
{
    public function testServe()
    {
        $resolver = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('asset-path')
            ->will($this->returnValue(__FILE__));

        $assetManager = new AssetManager($resolver);
        $request = new Request();
        $request->setUri('http://localhost/base-path/asset-path');
        $request->setBasePath('/base-path');
        ob_start();
        // need the error handler since headers will otherwise be considered as "already sent"
        ErrorHandler::start();
        $servedSuccess = $assetManager->serveAsset($request);
        ErrorHandler::stop();
        $served = ob_get_contents();
        ob_end_clean();
        $this->assertTrue($servedSuccess);
        $this->assertSame(file_get_contents(__FILE__), $served);
    }

    public function testWontServeOnResolveMiss()
    {
        $resolver = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('asset-path')
            ->will($this->returnValue(null));

        $assetManager = new AssetManager($resolver);
        $request = new Request();
        $request->setUri('http://localhost/base-path/asset-path');
        $request->setBasePath('/base-path');
        ob_start();
        $servedSuccess = $assetManager->serveAsset($request);
        $served = ob_get_contents();
        ob_end_clean();
        $this->assertFalse($servedSuccess);
        $this->assertEmpty($served);
    }

    public function testWontServeWithoutValidRequest()
    {
        $assetManager = new AssetManager($this->getMock('AssetManager\Resolver\ResolverInterface'));
        $request = new ConsoleRequest();
        ob_start();
        $servedSuccess = $assetManager->serveAsset($request);
        $served = ob_get_contents();
        ob_end_clean();
        $this->assertFalse($servedSuccess);
        $this->assertEmpty($served);
    }
}
