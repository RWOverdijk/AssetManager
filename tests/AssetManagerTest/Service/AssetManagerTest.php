<?php

namespace AssetManagerTest\Service;

use AssetManager\Service\AssetCacheManager;
use AssetManager\Service\AssetFilterManager;
use PHPUnit_Framework_TestCase;
use Assetic\Asset;
use AssetManager\Cache\FilePathCache;
use AssetManager\Service\AssetManager;
use AssetManager\Service\MimeResolver;
use AssetManager\Resolver\CollectionResolver;
use AssetManager\Resolver\AggregateResolver;
use Zend\Http\Response;
use Zend\Http\PhpEnvironment\Request;
use Zend\Console\Request as ConsoleRequest;

class AssetManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        require_once __DIR__ . '/../../_files/JSMin.inc';
        require_once __DIR__ . '/../../_files/CustomFilter.php';
        require_once __DIR__ . '/../../_files/BrokenFilter.php';
        require_once __DIR__ . '/../../_files/ReverseFilter.php';
    }

    protected function getRequest()
    {
        $request = new Request();
        $request->setUri('http://localhost/base-path/asset-path');
        $request->setBasePath('/base-path');

        return $request;
    }

    /**
     * @param string $resolveTo
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\AssetManager\Resolver\ResolverInterface
     */
    protected function getResolver($resolveTo = __FILE__)
    {
        $mimeResolver    = new MimeResolver;
        $asset           = new Asset\FileAsset($resolveTo);
        $asset->mimetype = $mimeResolver->getMimeType($resolveTo);
        $resolver        = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('asset-path')
            ->will($this->returnValue($asset));

        return $resolver;
    }

    public function getCollectionResolver()
    {
        $aggregateResolver  = new AggregateResolver;
        $mockedResolver     = $this->getResolver(__DIR__ . '/../../_files/require-jquery.js');
        $collArr = array(
            'blah.js' => array(
                'asset-path'
            )
        );
        $resolver = new CollectionResolver($collArr);
        $resolver->setAggregateResolver($aggregateResolver);

        $aggregateResolver->attach($mockedResolver, 500);
        $aggregateResolver->attach($resolver, 1000);

        return $resolver;
    }

    public function testConstruct()
    {
        $resolver     = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $assetManager = new AssetManager($resolver, array('herp', 'derp'));

        $this->assertSame($resolver, $assetManager->getResolver());
        $this->assertAttributeEquals(array('herp', 'derp'), 'config', $assetManager);
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testConstructFailsOnOtherType()
    {
        new AssetManager('invalid');
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
        $assetManager = new AssetManager($this->getResolver());
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
        new AssetManager('invalid');
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

        $assetFilterManager = new AssetFilterManager($config['filters']);
        $assetCacheManager = $this->getAssetCacheManagerMock();

        $response     = new Response;
        $resolver     = $this->getResolver(__DIR__ . '/../../_files/require-jquery.js');
        $request      = $this->getRequest();
        $assetManager = new AssetManager($resolver, $config);
        $minified     = \JSMin::minify(file_get_contents(__DIR__ . '/../../_files/require-jquery.js'));
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);
        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);
        $this->assertEquals($minified, $response->getBody());
    }

    public function testSetExtensionFilters()
    {
        $config = array(
            'filters' => array(
                'js' => array(
                    array(
                        'filter' => 'JSMin',
                    ),
                ),
            ),
        );

        $assetFilterManager = new AssetFilterManager($config['filters']);
        $assetCacheManager = $this->getAssetCacheManagerMock();

        $mimeResolver = new MimeResolver;
        $response     = new Response;
        $resolver     = $this->getResolver(__DIR__ . '/../../_files/require-jquery.js');
        $request      = $this->getRequest();
        $assetManager = new AssetManager($resolver, $config);
        $minified     = \JSMin::minify(file_get_contents(__DIR__ . '/../../_files/require-jquery.js'));
        $assetFilterManager->setMimeResolver($mimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);

        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);
        $this->assertEquals($minified, $response->getBody());
    }

    public function testSetExtensionFiltersNotDuplicate()
    {
        $config = array(
            'filters' => array(
                'js' => array(
                    array(
                        'filter' => '\ReverseFilter',
                    ),
                ),
            ),
        );

        $resolver           = $this->getCollectionResolver();
        $assetFilterManager = new AssetFilterManager($config['filters']);
        $mimeResolver       = new MimeResolver;
        $assetFilterManager->setMimeResolver($mimeResolver);
        $resolver->setAssetFilterManager($assetFilterManager);
        
        $response     = new Response;
        $request      = $this->getRequest();
        // Have to change uri because asset-path would cause an infinite loop
        $request->setUri('http://localhost/base-path/blah.js');
        
        $assetCacheManager = $this->getAssetCacheManagerMock();
        $assetManager      = new AssetManager($resolver->getAggregateResolver(), $config);
        $assetManager->setAssetCacheManager($assetCacheManager);
        $assetManager->setAssetFilterManager($assetFilterManager);

        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);

        $reversedOnlyOnce = '1' . strrev(file_get_contents(__DIR__ . '/../../_files/require-jquery.js'));
        $this->assertEquals($reversedOnlyOnce, $response->getBody());
    }

    public function testSetMimeTypeFilters()
    {
        $config = array(
            'filters' => array(
                'application/javascript' => array(
                    array(
                        'filter' => 'JSMin',
                    ),
                ),
            ),
        );

        $assetFilterManager = new AssetFilterManager($config['filters']);
        $assetCacheManager = $this->getAssetCacheManagerMock();

        $mimeResolver       = new MimeResolver;
        $response           = new Response;
        $resolver           = $this->getResolver(__DIR__ . '/../../_files/require-jquery.js');
        $request            = $this->getRequest();
        $assetManager       = new AssetManager($resolver, $config);
        $minified           = \JSMin::minify(file_get_contents(__DIR__ . '/../../_files/require-jquery.js'));
        $assetFilterManager->setMimeResolver($mimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);

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

        $assetFilterManager = new AssetFilterManager($config['filters']);
        $assetCacheManager = $this->getAssetCacheManagerMock();
        $mimeResolver       = new MimeResolver;
        $response           = new Response;
        $resolver           = $this->getResolver(__DIR__ . '/../../_files/require-jquery.js');
        $request            = $this->getRequest();
        $assetManager       = new AssetManager($resolver, $config);
        $assetFilterManager->setMimeResolver($mimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);

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

        $assetFilterManager = new AssetFilterManager($config['filters']);
        $assetCacheManager  = $this->getAssetCacheManagerMock();
        $mimeResolver       = new MimeResolver;
        $response           = new Response;
        $resolver           = $this->getResolver(__DIR__ . '/../../_files/require-jquery.js');
        $request            = $this->getRequest();
        $assetManager       = new AssetManager($resolver, $config);
        $assetFilterManager->setMimeResolver($mimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);

        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);
        $this->assertEquals(file_get_contents(__DIR__ . '/../../_files/require-jquery.js'), $response->getBody());
    }

    /**
     * @expectedException \AssetManager\Exception\RuntimeException
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

        $assetFilterManager = new AssetFilterManager($config['filters']);
        $assetCacheManager  = $this->getAssetCacheManagerMock();
        $mimeResolver       = new MimeResolver;
        $response           = new Response;
        $resolver           = $this->getResolver(__DIR__ . '/../../_files/require-jquery.js');
        $request            = $this->getRequest();
        $assetManager       = new AssetManager($resolver, $config);
        $assetFilterManager->setMimeResolver($mimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);
        $assetManager->resolvesToAsset($request);
        $assetManager->setAssetOnResponse($response);
    }

    public function testSetAssetOnResponse()
    {
        $assetFilterManager = new AssetFilterManager();
        $assetCacheManager  = $this->getAssetCacheManagerMock();
        $mimeResolver       = new MimeResolver;
        $assetManager       = new AssetManager($this->getResolver());
        $assetFilterManager->setMimeResolver($mimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);
        $request            = $this->getRequest();
        $assetManager->resolvesToAsset($request);
        $response           = $assetManager->setAssetOnResponse(new Response);

        $this->assertSame(file_get_contents(__FILE__), $response->getContent());
    }

    public function testAssetSetOnResponse()
    {
        $assetManager = new AssetManager($this->getResolver());
        $assetCacheManager = $this->getAssetCacheManagerMock();
        $this->assertFalse($assetManager->assetSetOnResponse());

        $assetFilterManager = new \AssetManager\Service\AssetFilterManager();
        $assetFilterManager->setMimeResolver(new MimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);
        $assetManager->resolvesToAsset($this->getRequest());
        $assetManager->setAssetOnResponse(new Response);

        $this->assertTrue($assetManager->assetSetOnResponse());
    }

    /**
     * @expectedException \AssetManager\Exception\RuntimeException
     */
    public function testSetAssetOnResponseNoMimeType()
    {
        $asset    = new Asset\FileAsset(__FILE__);
        $resolver = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('asset-path')
            ->will($this->returnValue($asset));

        $assetManager = new AssetManager($resolver);
        $request      = $this->getRequest();
        $assetManager->resolvesToAsset($request);

        $assetManager->setAssetOnResponse(new Response);
    }

    public function testResponseHeadersForAsset()
    {
        $mimeResolver       = new MimeResolver;
        $assetFilterManager = new AssetFilterManager();
        $assetCacheManager  = $this->getAssetCacheManagerMock();
        $assetManager       = new AssetManager($this->getResolver());
        $assetFilterManager->setMimeResolver($mimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);

        $request  = $this->getRequest();
        $assetManager->resolvesToAsset($request);
        $response = $assetManager->setAssetOnResponse(new Response);
        $thisFile = file_get_contents(__FILE__);

        if (function_exists('mb_strlen')) {
            $fileSize = mb_strlen($thisFile, '8bit');
        } else {
            $fileSize = strlen($thisFile);
        }

        $mimeType = $mimeResolver->getMimeType(__FILE__);

        $headers = 'Content-Transfer-Encoding: binary' . "\r\n";
        $headers .= 'Content-Type: ' . $mimeType . "\r\n";
        $headers .= 'Content-Length: ' . $fileSize . "\r\n";
        $this->assertSame($headers, $response->getHeaders()->toString());
    }

    /**
     * @expectedException \AssetManager\Exception\RuntimeException
     */
    public function testSetAssetOnReponseFailsWhenNotResolved()
    {
        $resolver     = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $assetManager = new AssetManager($resolver);

        $assetManager->setAssetOnResponse(new Response);
    }

    public function testResolvesToAssetNotFound()
    {
        $resolver        = $this->getMock('AssetManager\Resolver\ResolverInterface');
        $assetManager    = new AssetManager($resolver);
        $resolvesToAsset = $assetManager->resolvesToAsset(new Request);

        $this->assertFalse($resolvesToAsset);
    }

    public function testClearOutputBufferInSetAssetOnResponse()
    {
        $this->expectOutputString(file_get_contents(__FILE__));

        echo "This string would definately break any image source.\n";
        echo "This one would make it even worse.\n";
        echo "They all should be gone soon...\n";

        $assetFilterManager = new AssetFilterManager();
        $assetCacheManager  = $this->getAssetCacheManagerMock();
        $mimeResolver       = new MimeResolver;
        $assetManager       = new AssetManager($this->getResolver(), array('clear_output_buffer' => true));

        $assetFilterManager->setMimeResolver($mimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);
        $assetManager->resolvesToAsset($this->getRequest());

        $response = $assetManager->setAssetOnResponse(new Response);

        echo $response->getContent();
    }

    /**
     * @return string
     */
    public function getTmpDir()
    {
        $tmp = sys_get_temp_dir() . '/' . uniqid('am');

        mkdir($tmp);

        return $tmp;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAssetCacheManagerMock()
    {
        $assetCacheManager = $this->getMockBuilder('AssetManager\Service\AssetCacheManager')
            ->disableOriginalConstructor()
            ->getMock();

        $assetCacheManager->expects($this->any())
            ->method('setCache')
            ->will($this->returnCallback(
                function ($path, $asset) {
                    return $asset;
                }
            ));

        return $assetCacheManager;
    }
}
