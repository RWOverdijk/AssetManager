<?php

namespace AssetManagerTest\Service;

use Assetic\Asset\AssetCache;
use Assetic\Asset\FileAsset;
use Assetic\Cache\ApcCache;
use Assetic\Cache\CacheInterface;
use Assetic\Cache\FilesystemCache;
use AssetManager\Cache\FilePathCache;
use AssetManager\Service\AssetCacheManager;
use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;

/**
 * Test file for the Asset Cache Manager
 *
 * @package AssetManagerTest\Service
 */
class AssetCacheManagerTest extends TestCase
{
    /**
     * @covers \AssetManager\Service\AssetCacheManager::setCache
     */
    public function testSetCache()
    {
        $serviceManager = new ServiceManager();

        $config = array(
            'my/path' => array(
                'cache' => 'Apc',
            ),
        );

        $mockAsset = $this->getMockBuilder(FileAsset::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockAsset->mimetype = 'image/png';

        $assetManager = new AssetCacheManager($serviceManager, $config);
        $assetCache = $assetManager->setCache('my/path', $mockAsset);

        $this->assertTrue($assetCache instanceof AssetCache);
        $this->assertEquals($mockAsset->mimetype, $assetCache->mimetype);
    }

    /**
     * @covers \AssetManager\Service\AssetCacheManager::setCache
     */
    public function testSetCacheNoProviderFound()
    {
        $serviceManager = new ServiceManager();
        $config = array(
            'my/path' => array(
                'cache' => 'Apc',
            ),
        );

        $mockAsset = $this->getMockBuilder(FileAsset::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockAsset->mimetype = 'image/png';

        $assetManager = new AssetCacheManager($serviceManager, $config);
        $assetCache = $assetManager->setCache('not/defined', $mockAsset);

        $this->assertFalse($assetCache instanceof AssetCache);
    }

    /**
     * @covers \AssetManager\Service\AssetCacheManager::getProvider
     */
    public function testGetProvider()
    {
        $serviceManager = new ServiceManager();

        $config = array(
            'my/path' => array(
                'cache' => 'Apc',
            ),
        );

        $assetManager = new AssetCacheManager($serviceManager, $config);
        $reflectionMethod = new \ReflectionMethod(
            AssetCacheManager::class,
            'getProvider'
        );
        $reflectionMethod->setAccessible(true);

        $provider = $reflectionMethod->invoke($assetManager, 'my/path');

        $this->assertTrue($provider instanceof CacheInterface);
    }

    /**
     * @covers \AssetManager\Service\AssetCacheManager::getProvider
     */
    public function testGetProviderUsingDefaultConfiguration()
    {
        $serviceManager = new ServiceManager();
        $config = array(
            'default' => array(
                'cache' => 'Apc',
            ),
        );

        $assetManager = new AssetCacheManager($serviceManager, $config);
        $reflectionMethod = new \ReflectionMethod(
            AssetCacheManager::class,
            'getProvider'
        );
        $reflectionMethod->setAccessible(true);

        $provider = $reflectionMethod->invoke($assetManager, 'no/path');

        $this->assertTrue($provider instanceof CacheInterface);
    }

    /**
     * @covers \AssetManager\Service\AssetCacheManager::getProvider
     */
    public function testGetProviderWithDefinedService()
    {
        $serviceManager = new ServiceManager();

        $config = array(
            'default' => array(
                'cache' => 'myZf2Service',
            ),
        );

        $serviceManager->setFactory(
            'myZf2Service',
            function () {
                return new FilePathCache('somewhere', 'somfile');
            }
        );

        $assetManager = new AssetCacheManager($serviceManager, $config);
        $reflectionMethod = new \ReflectionMethod(
            AssetCacheManager::class,
            'getProvider'
        );
        $reflectionMethod->setAccessible(true);

        $provider = $reflectionMethod->invoke($assetManager, 'no/path');

        $this->assertTrue($provider instanceof FilePathCache);
    }

    /**
     * @covers \AssetManager\Service\AssetCacheManager::getProvider
     */
    public function testGetProviderWithCacheOptions()
    {
        $serviceManager = new ServiceManager();

        $config = array(
            'my_provided_class.tmp' => array(
                'cache' => FilePathCache::class,
                'options' => array(
                    'dir' => 'somewhere',
                )
            ),
        );

        $serviceManager->setFactory(
            'myZf2Service',
            function () {
                return new FilePathCache('somewhere', 'somfile');
            }
        );

        $assetManager = new AssetCacheManager($serviceManager, $config);
        $reflectionMethod = new \ReflectionMethod(
            AssetCacheManager::class,
            'getProvider'
        );
        $reflectionMethod->setAccessible(true);

        /** @var FilePathCache $provider */
        $provider = $reflectionMethod->invoke($assetManager, 'my_provided_class.tmp');
        $this->assertTrue($provider instanceof FilePathCache);

        $reflectionProperty = new \ReflectionProperty(FilePathCache::class, 'dir');
        $reflectionProperty->setAccessible(true);

        $this->assertTrue($reflectionProperty->getValue($provider) == 'somewhere');
    }

    /**
     * @covers \AssetManager\Service\AssetCacheManager::getProvider
     */
    public function testGetProviderWithMultipleDefinition()
    {
        $serviceManager = new ServiceManager();
        $config = array(
            'default' => array(
                'cache' => 'myZf2Service',
            ),

            'my_callback.tmp' => array(
                'cache' => function () {
                    return new FilePathCache('somewhere', 'somefile');
                },
            ),

            'my_provided_class.tmp' => array(
                'cache' => FilePathCache::class,
                'options' => array(
                    'dir' => 'somewhere',
                )
            ),

            'my_bc_check.tmp' => array(
                'cache' => 'Apc',
            ),
        );

        $serviceManager->setFactory(
            'myZf2Service',
            function () {
                return new FilePathCache('somewhere', 'somfile');
            }
        );

        $assetManager = new AssetCacheManager($serviceManager, $config);

        $reflectionMethod = new \ReflectionMethod(
            AssetCacheManager::class,
            'getProvider'
        );
        $reflectionMethod->setAccessible(true);

        $provider = $reflectionMethod->invoke($assetManager, 'no/path');
        $this->assertTrue($provider instanceof FilePathCache);

        $provider = $reflectionMethod->invoke($assetManager, 'my_callback.tmp');
        $this->assertTrue($provider instanceof FilePathCache);

        $provider = $reflectionMethod->invoke($assetManager, 'my_provided_class.tmp');
        $this->assertTrue($provider instanceof FilePathCache);

        $provider = $reflectionMethod->invoke($assetManager, 'my_bc_check.tmp');
        $this->assertTrue($provider instanceof ApcCache);
    }

    /**
     * @covers \AssetManager\Service\AssetCacheManager::getProvider
     */
    public function testGetProviderWithNoCacheConfig()
    {
        $serviceManager = new ServiceManager();

        $assetManager = new AssetCacheManager($serviceManager, array());
        $reflectionMethod = new \ReflectionMethod(
            AssetCacheManager::class,
            'getProvider'
        );
        $reflectionMethod->setAccessible(true);

        $provider = $reflectionMethod->invoke($assetManager, 'no/path');
        $this->assertNull($provider);
    }

    /**
     * @covers \AssetManager\Service\AssetCacheManager::getCacheProviderConfig
     */
    public function testGetCacheProviderConfig()
    {
        $expected = array(
            'cache' => FilePathCache::class,
            'options' => array(
                'dir' => 'somewhere',
            ),
        );

        $serviceManager = new ServiceManager();
        $config = array(
            'my_provided_class.tmp' => $expected,
        );

        $assetManager = new AssetCacheManager($serviceManager, $config);
        $reflectionMethod = new \ReflectionMethod(
            AssetCacheManager::class,
            'getCacheProviderConfig'
        );
        $reflectionMethod->setAccessible(true);

        $providerConfig = $reflectionMethod->invoke($assetManager, 'my_provided_class.tmp');
        $this->assertEquals($expected, $providerConfig);
    }

    /**
     * @covers \AssetManager\Service\AssetCacheManager::getCacheProviderConfig
     */
    public function testGetCacheProviderConfigReturnsDefaultCache()
    {
        $expected = array(
            'cache' => FilePathCache::class,
            'options' => array(
                'dir' => 'somewhere',
            ),
        );

        $serviceManager = new ServiceManager();
        $config = array(
            'default' => $expected,
            'some_other_definition' => array(
                'cache' => FilePathCache::class,
            )
        );

        $assetManager = new AssetCacheManager($serviceManager, $config);
        $reflectionMethod = new \ReflectionMethod(
            AssetCacheManager::class,
            'getCacheProviderConfig'
        );
        $reflectionMethod->setAccessible(true);

        $providerConfig = $reflectionMethod->invoke($assetManager, 'my_provided_class.tmp');
        $this->assertEquals($expected, $providerConfig);
    }

    /**
     * @covers \AssetManager\Service\AssetCacheManager::classMapper
     */
    public function testClassMapperResolvesApcCache()
    {
        $serviceManager = new ServiceManager();

        $assetManager = new AssetCacheManager($serviceManager, array());
        $reflectionMethod = new \ReflectionMethod(
            AssetCacheManager::class,
            'classMapper'
        );
        $reflectionMethod->setAccessible(true);

        $class = $reflectionMethod->invoke($assetManager, 'ApcCache');
        $this->assertEquals(ApcCache::class, $class);
    }

    /**
     * @covers \AssetManager\Service\AssetCacheManager::classMapper
     */
    public function testClassMapperResolvesFilesystemCache()
    {
        $serviceManager = new ServiceManager();

        $assetManager = new AssetCacheManager($serviceManager, array());
        $reflectionMethod = new \ReflectionMethod(
            AssetCacheManager::class,
            'classMapper'
        );
        $reflectionMethod->setAccessible(true);

        $class = $reflectionMethod->invoke($assetManager, 'FilesystemCache');
        $this->assertEquals(FilesystemCache::class, $class);
    }

    /**
     * @covers \AssetManager\Service\AssetCacheManager::classMapper
     */
    public function testClassMapperResolvesFilePathCache()
    {
        $serviceManager = new ServiceManager();

        $assetManager = new AssetCacheManager($serviceManager, array());

        $reflectionMethod = new \ReflectionMethod(
            AssetCacheManager::class,
            'classMapper'
        );
        $reflectionMethod->setAccessible(true);

        $class = $reflectionMethod->invoke($assetManager, 'FilePathCache');
        $this->assertEquals(FilePathCache::class, $class);
    }

    /**
     * @covers \AssetManager\Service\AssetCacheManager::classMapper
     */
    public function testClassMapperResolvesShorthandClassAlias()
    {
        $serviceManager = new ServiceManager();


        $assetManager = new AssetCacheManager($serviceManager, array());
        $reflectionMethod = new \ReflectionMethod(
            AssetCacheManager::class,
            'classMapper'
        );
        $reflectionMethod->setAccessible(true);

        $class = $reflectionMethod->invoke($assetManager, 'FilePath');
        $this->assertEquals(FilePathCache::class, $class);
    }
}
