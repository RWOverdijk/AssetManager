<?php

namespace AssetManagerTest\Service;

use PHPUnit_Framework_TestCase;
use AssetManager\Service\AliasPathStackResolverServiceFactory;
use Zend\ServiceManager\ServiceManager;

class AliasPathStackResolverServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * Mainly to avoid regressions
     */
    public function testCreateService()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'Config',
            array(
                'asset_manager' => array(
                    'resolver_configs' => array(
                        'aliases' => array(
                            'alias1/' => 'path1',
                            'alias2/' => 'path2',
                        ),
                    ),
                ),
            )
        );

        $factory = new AliasPathStackResolverServiceFactory();
        /* @var $resolver \AssetManager\Resolver\AliasPathStackResolver */
        $resolver = $factory->createService($serviceManager);
        $this->assertSame(
            array(
                'alias1/' => 'path1' . DIRECTORY_SEPARATOR,
                'alias2/' => 'path2' . DIRECTORY_SEPARATOR,
            ),
            $resolver->getAliases()
        );
    }

    /**
     * Mainly to avoid regressions
     */
    public function testCreateServiceWithNoConfig()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('Config', array());

        $factory = new AliasPathStackResolverServiceFactory();
        /* @var $resolver \AssetManager\Resolver\AliasPathStackResolver */
        $resolver = $factory->createService($serviceManager);
        $this->assertEmpty($resolver->getAliases());
    }
}
