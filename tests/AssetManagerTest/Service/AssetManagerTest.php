<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use AssetManager\Service\AssetManager;
use Zend\Http\Response;
use Zend\Http\PhpEnvironment\Request;
use Zend\Console\Request as ConsoleRequest;
use Zend\Stdlib\ErrorHandler;

class AssetManagerTest extends PHPUnit_Framework_TestCase
{
    protected function getTestAssetManager()
    {
        $resolver = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('asset-path')
            ->will($this->returnValue(__FILE__));

        return new AssetManager($resolver);
    }

    protected function getTestRequest()
    {
        $request = new Request();
        $request->setUri('http://localhost/base-path/asset-path');
        $request->setBasePath('/base-path');

        return $request;
    }

    public function testResolvesToAsset()
    {
        $assetManager    = $this->getTestAssetManager();
        $request         = $this->getTestRequest();
        $resolvesToAsset = $assetManager->resolvesToAsset($request);

        $this->assertTrue($resolvesToAsset);
    }

    public function testSetAssetOnResponse()
    {
        $assetManager    = $this->getTestAssetManager();
        $request         = $this->getTestRequest();
        $resolvesToAsset = $assetManager->resolvesToAsset($request);
        $response        = new Response();

        $response = $assetManager->setAssetOnResponse($response);

        $this->assertSame(file_get_contents(__FILE__), $response->getContent());
    }

    /**
    * @expectedException AssetManager\Exception\RuntimeException
    */
    public function testSetAssetOnReponseFailsWhenNotResolved()
    {
        $resolver        = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $assetManager    = new AssetManager($resolver);
        $request         = $this->getTestRequest();
        $response        = new Response();

        $response = $assetManager->setAssetOnResponse($response);
    }

    public function testResolvesToAssetNotFound()
    {
        $resolver        = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $assetManager    = new AssetManager($resolver);

        $resolvesToAsset = $assetManager->resolvesToAsset(new Request());

        $this->assertFalse($resolvesToAsset);
    }
}
