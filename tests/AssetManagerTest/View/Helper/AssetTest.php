<?php
namespace AssetManagerTest\View\Helper;

use AssetManager\Resolver\MapResolver;
use AssetManager\Resolver\MimeResolverAwareInterface;
use AssetManager\Service\MimeResolver;
use AssetManager\View\Helper\Asset;
use PHPUnit_Framework_TestCase as TestCase;

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
                'query_string'     => '_',
                'cache'            => null,
                'development_mode' => false,
            ),
            'caching' => array(
                'default' => array(
                    'cache' => 'AssetManager\Cache\FilePathCache',
                    'options' => array(
                        'dir' => 'public/assets',
                    ),
                ),
            ),
        );

        $configWithoutCache = array(
            'view_helper' => array(
                'query_string'     => '_',
                'cache'            => null,
                'development_mode' => false,
            ),
        );

        $filename = 'porn-food/bac.on';

        $resolver = $this->getGenericResolver();

        $resolver->setMap(array(
            'porn-food/bac.on' => __FILE__,
        ));

        $helperWithCache = new Asset($resolver, null, $configWithCache);
        $newFilename = $helperWithCache->__invoke($filename);

        $this->assertContains('?_=', $newFilename);

        $helperWithoutCache = new Asset($resolver, null, $configWithoutCache);
        $newFilename = $helperWithoutCache->__invoke($filename);

        $this->assertNotContains('?_=', $newFilename);
    }

    public function testSameResultWithoutCachingConfig()
    {
        $config = array(
            'view_helper' => array(
                'query_string'     => '_',
                'cache'            => null,
                'development_mode' => false,
            ),
        );

        $filename = 'porn-food/bac.on';

        $resolver = $this->getGenericResolver();

        $resolver->setMap(array(
            'porn-food/bac.on' => __FILE__,
        ));

        $helper = new Asset($resolver, null, $config);
        $newFilename = $helper->__invoke($filename);

        $this->assertSame($newFilename, $filename);
    }

    public function testDevelopmentModeWithoutCache()
    {
        $config = array(
            'view_helper' => array(
                'query_string'     => '_',
                'cache'            => null,
                'development_mode' => true,
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
    }
}
