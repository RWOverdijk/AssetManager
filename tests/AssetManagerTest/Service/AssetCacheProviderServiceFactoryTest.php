<?php

namespace AssetManagerTest\Service;

use Assetic\Cache\ApcCache;
use Assetic\Cache\CacheInterface;
use AssetManager\Cache\FilePathCache;
use PHPUnit_Framework_TestCase;
use AssetManager\Service\AssetCacheProviderServiceFactory;
use Zend\ServiceManager\ServiceManager;

class AssetCacheProviderServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Config',
            array(
                'asset_manager' => array(
                    'caching' => array(
                        'default' => array(
                            'cache' => 'Apc',
                        ),
                    ),
                ),
            )
        );

        $t = new AssetCacheProviderServiceFactory($serviceManager);

        $providers = $t->createService($serviceManager);

        $this->assertTrue($providers['default'] instanceof CacheInterface);
    }

    public function testCreateServiceWithCallback()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Config',
            array(
                'asset_manager' => array(
                    'caching' => array(
                        'default' => array(
                            'cache' => function () {
                                return new FilePathCache('somewhere', 'somfile');
                            },
                        ),
                    ),
                ),
            )
        );

        $t = new AssetCacheProviderServiceFactory($serviceManager);

        $providers = $t->createService($serviceManager);

        $this->assertTrue($providers['default'] instanceof FilePathCache);
    }

    public function testCreateServiceWithDefinedService()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Config',
            array(
                'asset_manager' => array(
                    'caching' => array(
                        'default' => array(
                            'cache' => 'myZf2Service',
                        ),
                    ),
                ),
            )
        );

        $serviceManager->setFactory('myZf2Service', function () {
            return new FilePathCache('somewhere', 'somfile');
        });

        $t = new AssetCacheProviderServiceFactory($serviceManager);

        $providers = $t->createService($serviceManager);

        $this->assertTrue($providers['default'] instanceof FilePathCache);
    }

    public function testCreateServiceWithMultipleDefinition()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Config',
            array(
                'asset_manager' => array(
                    'caching' => array(
                        'default' => array(
                            'cache' => 'myZf2Service',
                        ),

                        'my_callback.tmp' => array(
                            'cache' => function () {
                                return new FilePathCache('somewhere', 'somfile');
                            },
                        ),

                        'my_provided_class.tmp' => array(
                            'cache' => 'AssetManager\\Cache\\FilePathCache',
                            'options' => array(
                                'dir' => 'somewhere',
                            )
                        ),

                        'my_bc_check.tmp' => array(
                            'cache' => 'Apc',
                        ),
                    ),
                ),
            )
        );

        $serviceManager->setFactory('myZf2Service', function () {
            return new FilePathCache('somewhere', 'somfile');
        });

        $t = new AssetCacheProviderServiceFactory($serviceManager);

        $providers = $t->createService($serviceManager);

        $this->assertTrue($providers['default'] instanceof FilePathCache);
        $this->assertTrue($providers['my_callback.tmp'] instanceof FilePathCache);
        $this->assertTrue($providers['my_provided_class.tmp'] instanceof FilePathCache);
        $this->assertTrue($providers['my_bc_check.tmp'] instanceof ApcCache);
    }
}
