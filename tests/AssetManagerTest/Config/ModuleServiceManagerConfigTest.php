<?php

namespace AssetManagerTest\Config;

use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

/**
 * Test to ensure config file is properly setup and all services are retrievable
 *
 * @package AssetManagerTest\Config
 */
class ModuleServiceManagerConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test the Service Managers Factories.
     *
     * @coversNothing
     */
    public function testServiceManagerFactories()
    {
        $config = include __DIR__.'/../../../config/module.config.php';

        $serviceManagerConfig = new Config($config['service_manager']);
        $serviceManager = new ServiceManager($serviceManagerConfig);
        $serviceManager->setService('config', $config);

        foreach ($config['service_manager']['factories'] as $serviceName => $service) {
            $this->assertTrue($serviceManager->has($serviceName));

            //Make sure we can fetch the service
            $service = $serviceManager->get($serviceName);

            $this->assertTrue(is_object($service));
        }

    }

    /**
     * Test the Service Managers Invokables.
     *
     * @coversNothing
     */
    public function testServiceManagerInvokables()
    {
        $config = include __DIR__.'/../../../config/module.config.php';

        $serviceManagerConfig = new Config($config['service_manager']);
        $serviceManager = new ServiceManager($serviceManagerConfig);
        $serviceManager->setService('config', $config);

        foreach ($config['service_manager']['invokables'] as $serviceName => $service) {
            $this->assertTrue($serviceManager->has($serviceName));

            //Make sure we can fetch the service
            $service = $serviceManager->get($serviceName);

            $this->assertTrue(is_object($service));
        }
    }

    /**
     * Test the Service Managers Invokables.
     *
     * @coversNothing
     */
    public function testServiceManagerAliases()
    {
        $config = include __DIR__.'/../../../config/module.config.php';

        $serviceManagerConfig = new Config($config['service_manager']);
        $serviceManager = new ServiceManager($serviceManagerConfig);
        $serviceManager->setService('config', $config);

        foreach ($config['service_manager']['aliases'] as $serviceName => $service) {
            $this->assertTrue($serviceManager->has($serviceName));

            //Make sure we can fetch the service
            $service = $serviceManager->get($serviceName);

            $this->assertTrue(is_object($service));
        }
    }

    /**
     * Test for Issue #134 - Test for specific mime_resolver invokable
     *
     * @coversNothing
     */
    public function mimeResolverInvokableTest()
    {
        $config = include __DIR__.'/../../../config/module.config.php';

        $serviceManagerConfig = new Config($config['service_manager']);
        $serviceManager = new ServiceManager($serviceManagerConfig);
        $serviceManager->setService('config', $config);

        $this->assertTrue($serviceManager->has('AssetManager\Service\MimeResolver'));
        $this->assertTrue(is_object($serviceManager->get('AssetManager\Service\MimeResolver')));
    }

    /**
     * Test for Issue #134 - Test for specific mime_resolver alias
     *
     * @coversNothing
     */
    public function mimeResolverAliasTest()
    {
        $config = include __DIR__.'/../../../config/module.config.php';

        $serviceManagerConfig = new Config($config['service_manager']);
        $serviceManager = new ServiceManager($serviceManagerConfig);
        $serviceManager->setService('config', $config);

        $this->assertTrue($serviceManager->has('mime_resolver'));
        $this->assertTrue(is_object($serviceManager->get('mime_resolver')));
    }
}
