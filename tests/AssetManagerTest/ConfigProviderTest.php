<?php

namespace AssetManagerTest;

use AssetManager\ConfigProvider;
use Laminas\EventManager\Test\EventListenerIntrospectionTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AssetManager\Module
 */
class ConfigProviderTest extends TestCase
{
    use EventListenerIntrospectionTrait;

    public function testGetAutoloaderConfig(): void
    {
        $configProvider = new ConfigProvider();
        // just testing ZF specification requirements
        $this->assertIsArray($configProvider->__invoke());
    }
}
