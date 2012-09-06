<?php

namespace AssetManagerTest\Service;

require_once __DIR__ . '/../../_files/JSMin.php';
require_once __DIR__ . '/../../_files/CustomFilter.php';
require_once __DIR__ . '/../../_files/BrokenFilter.php';

use PHPUnit_Framework_TestCase;
use Assetic\Asset;
use AssetManager\Cache\FilePathCache;
use AssetManager\Service\AssetManager;
use AssetManager\Service\MimeResolver;
use Zend\Http\Response;
use Zend\Http\PhpEnvironment\Request;
use Zend\Console\Request as ConsoleRequest;
use Zend\Stdlib\ErrorHandler;

class AssetManagerTest extends PHPUnit_Framework_TestCase
{

    protected function getRequest()
    {
        $request = new Request();
        $request->setUri('http://localhost/base-path/asset-path');
        $request->setBasePath('/base-path');

        return $request;
    }

    protected function getResolver($resolveTo = __FILE__)
    {
        $mimeResolver    = new MimeResolver;
        $asset           = new Asset\FileAsset($resolveTo);
        $asset->mimetype = $mimeResolver->getMimeType($resolveTo);
        $resolver = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('asset-path')
            ->will($this->returnValue($asset));

        return $resolver;
    }

    public function testConstruct()
    {
        $resolver       = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $assetManager   = new AssetManager($resolver, array('herp', 'derp'));

        $this->assertSame($resolver, $assetManager->getResolver());
        $this->assertAttributeEquals(
            array('herp', 'derp'),
            'config',
            $assetManager
        );
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testConstructFailsOnOtherType()
    {
        $assetManager = new AssetManager('invalid');
    }

    public function testInvalidRequest()
    {
        $mimeResolver    = new MimeResolver;
        $asset           = new Asset\FileAsset(__FILE__);
        $asset->mimetype = $mimeResolver->getMimeType(__FILE__);
        $resolver        = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $resolver
            ->expects($this->any())
            ->method('resolve')
            ->with('asset-path')
            ->will($this->returnValue($asset));

        $request = new ConsoleRequest();

        $assetManager    = new AssetManager($resolver);
        $resolvesToAsset = $assetManager->resolvesToAsset($request);

        $this->assertFalse($resolvesToAsset);
    }

    public function testResolvesToAsset()
    {
        $assetManager    = new AssetManager($this->getResolver());
        $resolvesToAsset = $assetManager->resolvesToAsset($this->getRequest());

        $this->assertTrue($resolvesToAsset);
    }

    /*
     * Mock will throw error if called more than once
     */
    public function testResolvesToAssetCalledOnce()
    {
        $assetManager    = new AssetManager($this->getResolver());
        $assetManager->resolvesToAsset($this->getRequest());
        $assetManager->resolvesToAsset($this->getRequest());
    }

    public function testResolvesToAssetReturnsBoolean()
    {
        $assetManager    = new AssetManager($this->getResolver());
        $resolvesToAsset = $assetManager->resolvesToAsset($this->getRequest());

        $this->assertTrue(is_bool($resolvesToAsset));
    }

    /*
     * Test if works by checking if is same reference to instance
     */
    public function testSetResolver()
    {
        $assetManager = new AssetManager($this->getMock('AssetManager\Resolver\ResolverInterface'));

        $newResolver = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $assetManager->setResolver($newResolver);

        $this->assertSame($newResolver, $assetManager->getResolver());
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testSetResolverFailsOnInvalidType()
    {
        $assetManager = new AssetManager('invalid');
    }

    /*
     * Added for the sake of method coverage.
     */
    public function testGetResolver()
    {
        $resolver     = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $assetManager = new AssetManager($resolver);

        $this->assertSame($resolver, $assetManager->getResolver());
    }

    public function testSetStandardFilters()
    {
        $config = array(
            'filters' => array(
                'asset-path' => array(
                    array(
                        'filter' => 'JSMin',
                    ),
                ),
            ),
        );

        $mimeResolver   = new MimeResolver;
        $response       = new Response;
        $resolver       = $this->getResolver(__DIR__ . '/../../_files/require-jquery.js');
        $request        = $this->getRequest();
        $assetManager   = new AssetManager($resolver, $config);
        $minified       = \JSMin::minify(file_get_contents(__DIR__ . '/../../_files/require-jquery.js'));

        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);
        $this->assertEquals($minified, $response->getBody());

    }

    public function testCustomFilters()
    {
        $config = array(
            'filters' => array(
                'asset-path' => array(
                    array(
                        'filter' => new \CustomFilter,
                    ),
                ),
            ),
        );

        $mimeResolver   = new MimeResolver;
        $response       = new Response;
        $resolver       = $this->getResolver(__DIR__ . '/../../_files/require-jquery.js');
        $request        = $this->getRequest();
        $assetManager   = new AssetManager($resolver, $config);

        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);
        $this->assertEquals('called', $response->getBody());
    }

    public function testSetEmptyFilters()
    {
        $config = array(
            'filters' => array(
                'asset-path' => array(
                ),
            ),
        );

        $mimeResolver   = new MimeResolver;
        $response       = new Response;
        $resolver       = $this->getResolver(__DIR__ . '/../../_files/require-jquery.js');
        $request        = $this->getRequest();
        $assetManager   = new AssetManager($resolver, $config);

        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);
        $this->assertEquals(file_get_contents(__DIR__ . '/../../_files/require-jquery.js'), $response->getBody());
    }

    /**
     * @expectedException AssetManager\Exception\RuntimeException
     */
    public function testSetFalseClassFilter()
    {
        $config = array(
            'filters' => array(
                'asset-path' => array(
                    array(
                        'filter' => 'Bacon',
                    ),
                ),
            ),
        );

        $mimeResolver   = new MimeResolver;
        $response       = new Response;
        $resolver       = $this->getResolver(__DIR__ . '/../../_files/require-jquery.js');
        $request        = $this->getRequest();
        $assetManager   = new AssetManager($resolver, $config);

        $assetManager->resolvesToAsset($request);
        $assetManager->setAssetOnResponse($response);
    }

    public function testSetStandardCacheDefault()
    {
        $config = array(
            'caching' => array(

                'default' => array(
                    'cache'     => 'FilePath',
                    'options' => array(
                        'dir' => '/tmp',
                    ),
                ),
            ),
        );

        $mimeResolver   = new MimeResolver;
        $response       = new Response;
        $resolver       = $this->getResolver();
        $request        = $this->getRequest();
        $assetManager   = new AssetManager($resolver, $config);

        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);
        $this->assertEquals(file_get_contents(__FILE__), $response->getBody());

    }

    public function testSetStandardCacheAssetSpecific()
    {
        $config = array(
            'caching' => array(

                'asset-path' => array(
                    'cache'     => 'FilePath',
                    'options' => array(
                        'dir' => '/tmp',
                    ),
                ),
            ),
        );

        $mimeResolver   = new MimeResolver;
        $response       = new Response;
        $resolver       = $this->getResolver();
        $request        = $this->getRequest();
        $assetManager   = new AssetManager($resolver, $config);

        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);
        $this->assertEquals(file_get_contents(__FILE__), $response->getBody());

    }

    public function testSetStandardCacheEmpty()
    {
        $config = array(
            'caching' => array(

                'asset-path' => array(
                    'cache'     => '',
                ),
            ),
        );

        $mimeResolver   = new MimeResolver;
        $response       = new Response;
        $resolver       = $this->getResolver();
        $request        = $this->getRequest();
        $assetManager   = new AssetManager($resolver, $config);

        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);
        $this->assertEquals(file_get_contents(__FILE__), $response->getBody());

    }

    public function testSetDefaultCacheFileSystem()
    {
        $config = array(
            'caching' => array(

                'asset-path' => array(
                    'cache'     => 'FileSystem',
                    'options' => array(
                        'dir' => '/tmp',
                    ),
                ),
            ),
        );

        $mimeResolver   = new MimeResolver;
        $response       = new Response;
        $resolver       = $this->getResolver();
        $request        = $this->getRequest();
        $assetManager   = new AssetManager($resolver, $config);

        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);
        $this->assertEquals(file_get_contents(__FILE__), $response->getBody());
    }

    public function testSetCallbackCache()
    {
        $config = array(
            'caching' => array(

                'asset-path' => array(
                    'cache'     => function($file) {
                        return new FilePathCache('/tmp', $file);
                    },
                ),
            ),
        );

        $mimeResolver   = new MimeResolver;
        $response       = new Response;
        $resolver       = $this->getResolver();
        $request        = $this->getRequest();
        $assetManager   = new AssetManager($resolver, $config);

        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);
        $this->assertEquals(file_get_contents(__FILE__), $response->getBody());

    }

    public function testSetCallbackCacheInvalid()
    {
        $config = array(
            'caching' => array(

                'asset-path' => array(
                    'cache'     => function($file) {
                        return new \stdClass;
                    },
                ),
            ),
        );

        $mimeResolver   = new MimeResolver;
        $response       = new Response;
        $resolver       = $this->getResolver();
        $request        = $this->getRequest();
        $assetManager   = new AssetManager($resolver, $config);

        $assetManager->resolvesToAsset($request);
        $assetManager->setAssetOnResponse($response);
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

    public function testSetAssetOnResponse()
    {
        $assetManager    = new AssetManager($this->getResolver());
        $request         = $this->getRequest();
        $resolvesToAsset = $assetManager->resolvesToAsset($request);
        $response        = new Response();

        $response = $assetManager->setAssetOnResponse($response);

        $this->assertSame(file_get_contents(__FILE__), $response->getContent());
    }

    /**
     * @expectedException AssetManager\Exception\RuntimeException
     */
    public function testSetAssetOnResponseNoMimeType()
    {
        $asset           = new Asset\FileAsset(__FILE__);
        $resolver = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('asset-path')
            ->will($this->returnValue($asset));

        $assetManager    = new AssetManager($resolver);
        $request         = $this->getRequest();
        $resolvesToAsset = $assetManager->resolvesToAsset($request);
        $response        = new Response();

        $response = $assetManager->setAssetOnResponse($response);
    }

    public function testResponseHeadersForAsset()
    {
        $assetManager    = new AssetManager($this->getResolver());
        $request         = $this->getRequest();
        $resolvesToAsset = $assetManager->resolvesToAsset($request);
        $response        = new Response();
        $response        = $assetManager->setAssetOnResponse($response);
        $thisFile        = file_get_contents(__FILE__);

        if (function_exists('mb_strlen')) {
            $fileSize = mb_strlen($thisFile, '8bit');
        } else {
            $fileSize = strlen($thisFile);
        }

        $mimeResolver = new MimeResolver;
        $mimeType     = $mimeResolver->getMimeType(__FILE__);

        $headers = 'Content-Transfer-Encoding: binary' . "\r\n";
        $headers .= 'Content-Type: '.$mimeType . "\r\n";
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
        $request         = $this->getRequest();
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

    protected function resolve(RequestInterface $request)
    {
        if (!$request instanceof Request) {
            return false;
        }

        /* @var $request Request */
        /* @var $uri \Zend\Uri\UriInterface */
        $uri        = $request->getUri();
        $fullPath   = $uri->getPath();
        $path       = substr($fullPath, strlen($request->getBasePath()) + 1);
        $this->path = $path;
        $asset      = $this->getResolver()->resolve($path);

        if (!$asset instanceof AssetInterface) {
            return false;
        }

        return $asset;
    }
}
