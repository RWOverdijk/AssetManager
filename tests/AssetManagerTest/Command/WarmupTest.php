<?php

namespace AssetManagerTest\Command;

use AssetManager\Command\Warmup;
use AssetManager\Resolver\MapResolver;
use AssetManager\Resolver\ResolverInterface;
use AssetManager\Service\AssetCacheManager;
use AssetManager\Service\AssetFilterManager;
use AssetManager\Service\AssetManager;
use AssetManager\Service\MimeResolver;
use Laminas\ServiceManager\ServiceLocatorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

use function sys_get_temp_dir;
use function time;
use function trim;

class WarmupTest extends TestCase
{
    protected static string $assetName;

    public static function setUpBeforeClass(): void
    {
        self::$assetName = '_assettest.' . time();
    }

    public function setUp(): void
    {
        require_once __DIR__ . '/../../_files/JSMin.inc';
    }

    protected function tearDown(): void
    {
        $this->commandTester = null;
    }

    protected function getResolver(): ResolverInterface
    {
        $mimeResolver = new MimeResolver();
        $resolver = new MapResolver(
            [
                self::$assetName => __DIR__ . '/../../_files/require-jquery.js'
            ]
        );
        $resolver->setMimeResolver($mimeResolver);
        return $resolver;
    }

    protected function getAssetCacheManager(): AssetCacheManager
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $config = [
            self::$assetName => [
                'cache' => 'FilePathCache',
                'options' => [
                    'dir' => sys_get_temp_dir()
                ]
            ]
        ];
        return new AssetCacheManager($serviceLocator, $config);
    }

    public function testWarmupAction(): void
    {
        $config = [
            'filters' => [
                self::$assetName => [
                    [
                        'filter' => 'JSMin',
                    ]
                ]
            ]
        ];

        $assetFilterManager = new AssetFilterManager($config['filters']);
        $assetCacheManager = $this->getAssetCacheManager();

        $resolver = $this->getResolver();
        $assetManager = new AssetManager($resolver, $config);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);

        $application = new Application();
        $application->add(new Warmup($assetManager, []));
        $commandTester = new CommandTester($application->find('warmup'));
        $commandTester->execute([]);

        $this->assertEmpty(trim($commandTester->getDisplay()));
        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testWarmupActionWithVerbosity(): void
    {
        $config = [
            'filters' => [
                self::$assetName => [
                    [
                        'filter' => 'JSMin',
                    ]
                ]
            ]
        ];

        $assetFilterManager = new AssetFilterManager($config['filters']);
        $assetCacheManager = $this->getAssetCacheManager();

        $resolver = $this->getResolver();
        $assetManager = new AssetManager($resolver, $config);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);

        $application = new Application();
        $application->add(new Warmup($assetManager, []));
        $commandTester = new CommandTester($application->find('warmup'));
        $commandTester->execute(['--verbose' => true]);

        $this->assertNotEmpty(trim($commandTester->getDisplay()));
        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }
}
