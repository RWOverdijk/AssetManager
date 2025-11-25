<?php

namespace AssetManagerTest\Service;

use Assetic\Asset;
use AssetManager\Exception\RuntimeException;
use AssetManager\Resolver\AggregateResolver;
use AssetManager\Resolver\CollectionResolver;
use AssetManager\Resolver\ResolverInterface;
use AssetManager\Service\AssetCacheManager;
use AssetManager\Service\AssetFilterManager;
use AssetManager\Service\AssetManager;
use AssetManager\Service\MimeResolver;
use CustomFilter;
use JSMin;
use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AssetManagerTest extends TestCase
{
    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        require_once __DIR__ . '/../../_files/JSMin.inc';
        require_once __DIR__ . '/../../_files/CustomFilter.php';
        require_once __DIR__ . '/../../_files/BrokenFilter.php';
        require_once __DIR__ . '/../../_files/ReverseFilter.php';
    }

    protected function getRequest()
    {
        $request = (new Request())
            ->withUri(new Uri('http://localhost/base-path/asset-path'));
//        $request->setUri('http://localhost/base-path/asset-path');
//        $request->setBasePath('/base-path');

        return $request;
    }

    /**
     * @param string $resolveTo
     *
     * @return MockObject|ResolverInterface
     */
    protected function getResolver($resolveTo = __FILE__)
    {
        $mimeResolver = new MimeResolver;
        $asset        = new Asset\FileAsset($resolveTo);
//        $asset->mimetype = $mimeResolver->getMimeType($resolveTo);
        $resolver = $this->createMock(ResolverInterface::class);
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('asset-path')
            ->will($this->returnValue($asset));

        return $resolver;
    }

    public function getCollectionResolver()
    {
        $aggregateResolver = new AggregateResolver;
        $mockedResolver    = $this->getResolver(__DIR__ . '/../../_files/require-jquery.js');
        $collArr           = [
            'blah.js' => [
                'asset-path',
            ],
        ];
        $resolver          = new CollectionResolver($collArr);
        $resolver->setAggregateResolver($aggregateResolver);

        $aggregateResolver->attach($mockedResolver, 500);
        $aggregateResolver->attach($resolver, 1000);

        return $resolver;
    }

    public function testConstruct(): void
    {
        $resolver     = $this->createMock(ResolverInterface::class);
        $assetManager = new AssetManager($resolver, ['herp', 'derp']);

        $refClass   = new ReflectionClass(AssetManager::class);
        $configProp = $refClass->getProperty('config');
        $configProp->setAccessible(true);

        $this->assertSame($resolver, $assetManager->getResolver());
        $this->assertSame(['herp', 'derp'], $configProp->getValue($assetManager));
    }

    public function testConstructFailsOnOtherType(): void
    {
        $this->expectException('\TypeError');

        new AssetManager('invalid');
    }


    public function testResolvesToAsset(): void
    {
        $assetManager    = new AssetManager($this->getResolver());
        $resolvesToAsset = $assetManager->resolvesToAsset($this->getRequest());

        $this->assertTrue($resolvesToAsset);
    }

    /*
     * Mock will throw error if called more than once
     */

    public function testResolvesToAssetCalledOnce(): void
    {
        $assetManager = new AssetManager($this->getResolver());
        $assetManager->resolvesToAsset($this->getRequest());
        $assetManager->resolvesToAsset($this->getRequest());
    }

    public function testResolvesToAssetReturnsBoolean(): void
    {
        $assetManager    = new AssetManager($this->getResolver());
        $resolvesToAsset = $assetManager->resolvesToAsset($this->getRequest());

        $this->assertTrue(is_bool($resolvesToAsset));
    }

    /*
     * Test if works by checking if is same reference to instance
     */

    public function testSetResolver(): void
    {
        $assetManager = new AssetManager($this->createMock(ResolverInterface::class));

        $newResolver = $this->createMock(ResolverInterface::class);
        $assetManager->setResolver($newResolver);

        $this->assertSame($newResolver, $assetManager->getResolver());
    }

    public function testSetResolverFailsOnInvalidType(): void
    {
        $this->expectError();
        if (PHP_MAJOR_VERSION >= 7) {
            $this->expectException('\TypeError');
        }

        new AssetManager('invalid');
    }

    /*
     * Added for the sake of method coverage.
     */

    public function testGetResolver(): void
    {
        $resolver     = $this->createMock(ResolverInterface::class);
        $assetManager = new AssetManager($resolver);

        $this->assertSame($resolver, $assetManager->getResolver());
    }

    public function testSetStandardFilters(): void
    {
        $config = [
            'filters' => [
                'asset-path' => [
                    [
                        'filter' => 'JSMin',
                    ],
                ],
            ],
        ];

        $assetFilterManager = new AssetFilterManager($config['filters']);
        $assetCacheManager  = $this->getAssetCacheManagerMock();

        $response     = new Response;
        $resolver     = $this->getResolver(__DIR__ . '/../../_files/require-jquery.js');
        $request      = $this->getRequest();
        $assetManager = new AssetManager($resolver, $config);
        $minified     = JSMin::minify(file_get_contents(__DIR__ . '/../../_files/require-jquery.js'));
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);
        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);
        $this->assertEquals($minified, $response->getBody());
    }

    public function testSetExtensionFilters(): void
    {
        $config = [
            'filters' => [
                'js' => [
                    [
                        'filter' => 'JSMin',
                    ],
                ],
            ],
        ];

        $assetFilterManager = new AssetFilterManager($config['filters']);
        $assetCacheManager  = $this->getAssetCacheManagerMock();

        $mimeResolver = new MimeResolver;
        $response     = new Response;
        $resolver     = $this->getResolver(__DIR__ . '/../../_files/require-jquery.js');
        $request      = $this->getRequest();
        $assetManager = new AssetManager($resolver, $config);
        $minified     = JSMin::minify(file_get_contents(__DIR__ . '/../../_files/require-jquery.js'));
        $assetFilterManager->setMimeResolver($mimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);

        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);
        $this->assertEquals($minified, $response->getBody());
    }

    public function testSetExtensionFiltersNotDuplicate(): void
    {
        $config = [
            'filters' => [
                'js' => [
                    [
                        'filter' => '\ReverseFilter',
                    ],
                ],
            ],
        ];

        $resolver           = $this->getCollectionResolver();
        $assetFilterManager = new AssetFilterManager($config['filters']);
        $mimeResolver       = new MimeResolver;
        $assetFilterManager->setMimeResolver($mimeResolver);
        $resolver->setAssetFilterManager($assetFilterManager);

        $response = new Response();
        $request  = $this->getRequest();
        // Have to change uri because asset-path would cause an infinite loop
        $request->withUri(new Uri('http://localhost/base-path/blah.js'));

        $assetCacheManager = $this->getAssetCacheManagerMock();
        $assetManager      = new AssetManager($resolver->getAggregateResolver(), $config);
        $assetManager->setAssetCacheManager($assetCacheManager);
        $assetManager->setAssetFilterManager($assetFilterManager);

        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);

        $reversedOnlyOnce = '1' . strrev(file_get_contents(__DIR__ . '/../../_files/require-jquery.js'));
        $this->assertEquals($reversedOnlyOnce, $response->getBody());
    }

    public function testSetMimeTypeFilters(): void
    {
        $config = [
            'filters' => [
                'application/javascript' => [
                    [
                        'filter' => 'JSMin',
                    ],
                ],
            ],
        ];

        $assetFilterManager = new AssetFilterManager($config['filters']);
        $assetCacheManager  = $this->getAssetCacheManagerMock();

        $mimeResolver = new MimeResolver;
        $response     = new Response;
        $resolver     = $this->getResolver(__DIR__ . '/../../_files/require-jquery.js');
        $request      = $this->getRequest();
        $assetManager = new AssetManager($resolver, $config);
        $minified     = JSMin::minify(file_get_contents(__DIR__ . '/../../_files/require-jquery.js'));
        $assetFilterManager->setMimeResolver($mimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);

        $this->assertTrue($assetManager->resolvesToAsset($request));
        $assetManager->setAssetOnResponse($response);
        $this->assertEquals($minified, $response->getBody());
    }

    public function testCustomFilters(): void
    {
        $config = [
            'filters' => [
                'asset-path' => [
                    [
                        'filter' => new CustomFilter,
                    ],
                ],
            ],
        ];

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
        $this->assertEquals('called', $response->getBody());
    }

    public function testSetEmptyFilters(): void
    {
        $config = [
            'filters' => [
                'asset-path' => [
                ],
            ],
        ];

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

    public function testSetFalseClassFilter(): void
    {
        $this->expectException(RuntimeException::class);
        $config = [
            'filters' => [
                'asset-path' => [
                    [
                        'filter' => 'Bacon',
                    ],
                ],
            ],
        ];

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

    public function testSetAssetOnResponse(): void
    {
        $assetFilterManager = new AssetFilterManager();
        $assetCacheManager  = $this->getAssetCacheManagerMock();
        $mimeResolver       = new MimeResolver;
        $assetManager       = new AssetManager($this->getResolver());
        $assetFilterManager->setMimeResolver($mimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);
        $request = $this->getRequest();
        $assetManager->resolvesToAsset($request);
        $response = $assetManager->setAssetOnResponse(new Response);

        $this->assertSame(file_get_contents(__FILE__), $response->getContent());
    }

    public function testAssetSetOnResponse(): void
    {
        $assetManager      = new AssetManager($this->getResolver());
        $assetCacheManager = $this->getAssetCacheManagerMock();
        $this->assertFalse($assetManager->assetSetOnResponse());

        $assetFilterManager = new AssetFilterManager();
        $assetFilterManager->setMimeResolver(new MimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);
        $assetManager->resolvesToAsset($this->getRequest());
        $assetManager->setAssetOnResponse(new Response);

        $this->assertTrue($assetManager->assetSetOnResponse());
    }

    public function testSetAssetOnResponseNoMimeType(): void
    {
        $this->expectException(RuntimeException::class);
        $asset    = new Asset\FileAsset(__FILE__);
        $resolver = $this->createMock(ResolverInterface::class);
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

    public function testResponseHeadersForAsset(): void
    {
        $mimeResolver       = new MimeResolver;
        $assetFilterManager = new AssetFilterManager();
        $assetCacheManager  = $this->getAssetCacheManagerMock();
        $assetManager       = new AssetManager($this->getResolver());
        $assetFilterManager->setMimeResolver($mimeResolver);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);

        $request = $this->getRequest();
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

    public function testSetAssetOnReponseFailsWhenNotResolved(): void
    {
        $this->expectException(RuntimeException::class);
        $resolver     = $this->createMock(ResolverInterface::class);
        $assetManager = new AssetManager($resolver);

        $assetManager->setAssetOnResponse(new Response);
    }

    public function testResolvesToAssetNotFound(): void
    {
        $resolver        = $this->createMock(ResolverInterface::class);
        $assetManager    = new AssetManager($resolver);
        $resolvesToAsset = $assetManager->resolvesToAsset(new Request);

        $this->assertFalse($resolvesToAsset);
    }

    public function testClearOutputBufferInSetAssetOnResponse(): void
    {
        $this->expectOutputString(file_get_contents(__FILE__));

        echo "This string would definately break any image source.\n";
        echo "This one would make it even worse.\n";
        echo "They all should be gone soon...\n";

        $assetFilterManager = new AssetFilterManager();
        $assetCacheManager  = $this->getAssetCacheManagerMock();
        $mimeResolver       = new MimeResolver;
        $assetManager       = new AssetManager($this->getResolver(), ['clear_output_buffer' => true]);

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
     * @return MockObject
     */
    protected function getAssetCacheManagerMock()
    {
        $assetCacheManager = $this->getMockBuilder(AssetCacheManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $assetCacheManager->expects($this->any())
            ->method('setCache')
            ->will(
                $this->returnCallback(
                    function ($path, $asset) {
                        return $asset;
                    },
                ),
            );

        return $assetCacheManager;
    }
}
