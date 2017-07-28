<?php
namespace AssetManagerTest\View\Helper;

use AssetManager\Core\Cache\FilePathCache;
use AssetManager\Core\Resolver\MapResolver;
use AssetManager\Core\Resolver\MimeResolverAwareInterface;
use AssetManager\Core\Service\MimeResolver;
use AssetManager\View\Helper\Asset;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use Zend\View\HelperPluginManager;

class AssetTest extends TestCase
{
    private function getGenericResolver()
    {
        $resolver = new MapResolver;

        $this->assertTrue($resolver instanceof MimeResolverAwareInterface);

        $mimeResolver = new MimeResolver;

        $resolver->setMimeResolver($mimeResolver);

        return $resolver;
    }

    public function testInvoke()
    {
        $configWithCache = array(
            'view_helper' => array(
                'append_timestamp' => true,
                'query_string'     => '_',
                'cache'            => null,
            ),
            'caching' => array(
                'default' => array(
                    'cache' => FilePathCache::class,
                    'options' => array(
                        'dir' => 'public/assets',
                    ),
                ),
            ),
        );

        $configWithoutCache = array(
            'view_helper' => array(
                'append_timestamp' => true,
                'query_string'     => '_',
                'cache'            => null,
            ),
        );

        $filename = 'porn-food/bacon.php';

        $resolver = $this->getGenericResolver();

        $resolver->setMap(array(
            'porn-food/bacon.php' => __FILE__,
        ));

        $helperWithCache = new Asset($resolver, null, $configWithCache);
        $newFilenameWithCache = $helperWithCache->__invoke($filename);

        // with cache file should have a timestamp query param
        $this->assertContains('?_=', $newFilenameWithCache);

        $helperWithoutCache = new Asset($resolver, null, $configWithoutCache);
        $newFilenameWithoutCache = $helperWithoutCache->__invoke($filename);

        // without cache file should have a timestamp query param
        $this->assertContains('?_=', $newFilenameWithoutCache);
    }

    public function testSameResultWithoutCachingConfig()
    {
        $config = array(
            'view_helper' => array(
                'append_timestamp' => true,
                'query_string'     => '_',
                'cache'            => null,
            ),
        );

        $filename = 'porn-food/bac.on';

        $resolver = $this->getGenericResolver();

        $resolver->setMap(array(
            'porn-food/bac.on' => __FILE__,
        ));

        $helper = new Asset($resolver, null, $config);
        $newFilename = $helper->__invoke($filename);

        $this->assertContains('?_=', $newFilename);
        $this->assertNotSame($newFilename, $filename);
    }

    public function testForceToNotAppendTimestampWithoutCache()
    {
        $config = array(
            'view_helper' => array(
                'append_timestamp' => false,
                'query_string'     => '_',
                'cache'            => null,
            ),
        );

        $filename = 'porn-food/bac.on';

        $resolver = $this->getGenericResolver();

        $resolver->setMap(array(
            'porn-food/bac.on' => __FILE__,
        ));

        $helper = new Asset($resolver, null, $config);
        $newFilename = $helper->__invoke($filename);

        $this->assertNotContains('?_=', $newFilename);
        $this->assertSame($newFilename, $filename);
    }
    
    public function testRetrieveHelperFromPluginManagerByClassConstant()
    {
        $config = require __DIR__.'/../../../../config/module.config.php';
        
        $serviceConfig = new Config($config['service_manager']);
        $serviceManager = new ServiceManager();
        $serviceManager->setService('config', $config);
        $serviceConfig->configureServiceManager($serviceManager);
        
        $pluginManager = new HelperPluginManager($serviceManager, $config['view_helpers']);
        $this->assertInstanceOf(Asset::class, $pluginManager->get(Asset::class));
    }
}
