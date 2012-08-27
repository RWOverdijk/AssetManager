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

    /**
    * @expectedException \PHPUnit_Framework_Error
    */
    public function testSetResolverFails()
    {
        $resolver = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $assetManager = new AssetManager($resolver);

        $assetManager->setResolver('abc');
        $assetManager->setResolver(1234);
        $assetManager->setResolver(new \StdClass);
    }

    public function testSetResolver()
    {
        $resolver = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $assetManager = new AssetManager($resolver);

        $this->assertNull($assetManager->setResolver($resolver));
    }

    /**
    * @expectedException \PHPUnit_Framework_Error
    */
    public function testConstructFailsOnOtherType()
    {
        $assetManager = new AssetManager();
        $assetManager = new AssetManager('abc');
        $assetManager = new AssetManager(1234);
        $assetManager = new AssetManager(new \stdClass);
    }

    /**
    * @expectedException \PHPUnit_Framework_Error
    */
    public function testResolvesToAssetType()
    {
        $resolver = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $assetManager = new AssetManager($resolver);

        $assetManager->resolvesToAsset('abc');
        $assetManager->resolvesToAsset(1234);
        $assetManager->resolvesToAsset(new \StdClass);
    }

    public function testGetAssetAssertNull()
    {
        $resolver        = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $assetManager    = new AssetManager($resolver);

        $this->assertNull($assetManager->getAsset());
    }

    public function testGetAssetAssertAsset()
    {
        $assetManager    = $this->getTestAssetManager();
        $request         = $this->getTestRequest();
        $resolvesToAsset = $assetManager->resolvesToAsset($request);

        $this->assertSame(file_get_contents(__FILE__), $assetManager->getAsset());
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
    public function testAddAsset()
    {
        $resolver = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $resolver
            ->expects($this->any())
            ->method('resolve')
            ->with('asset-path')
            ->will($this->returnValue(__FILE__ . 'a'));

        $assetManager = new AssetManager($resolver);

        $request         = $this->getTestRequest();
        $resolvesToAsset = $assetManager->resolvesToAsset($request);
        $response        = new Response();

        $response = $assetManager->setAssetOnResponse($response);
    }

    public function testResponseHeadersForAsset()
    {
        $assetManager    = $this->getTestAssetManager();
        $request         = $this->getTestRequest();
        $resolvesToAsset = $assetManager->resolvesToAsset($request);
        $response        = new Response();

        $response = $assetManager->setAssetOnResponse($response);
        $thisFile = file_get_contents(__FILE__);

        if (function_exists('mb_strlen')) {
            $fileSize = mb_strlen($thisFile, '8bit');
        } else {
            $fileSize = strlen($thisFile);
        }
        $headers = 'Content-Transfer-Encoding: binary' . "\r\n";
        $headers .= 'Content-Type: text/x-php; charset=us-ascii' . "\r\n";
        $headers .= 'Content-Length: ' . $fileSize . "\r\n";

        $this->assertSame($headers, $response->getHeaders()->toString());
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
