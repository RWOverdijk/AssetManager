<?php

namespace AssetManagerTest\Controller;

use AssetManager\Controller\ConsoleController;
use AssetManager\Core\Resolver\MapResolver;
use AssetManager\Core\Service\AssetCacheManager;
use AssetManager\Core\Service\AssetFilterManager;
use AssetManager\Core\Service\AssetManager;
use AssetManager\Core\Service\MimeResolver;
use JSMin;
use PHPUnit_Framework_TestCase;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\Request as ConsoleRequest;
use Zend\Mvc\Console\Router\RouteMatch;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch as V2RouteMatch;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Resolver\ResolverInterface;

class ConsoleControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     * @var ConsoleController
     */
    protected $controller;
    protected $request;
    protected $routeMatch;
    protected $event;
    protected static $assetName;

    public static function setUpBeforeClass()
    {
        self::$assetName = '_assettest.' . time();
    }

    public function setUp()
    {
        require_once __DIR__ . '/../../_files/JSMin.inc';

        $config = array(
            'filters' => array(
                self::$assetName => array(
                    array(
                        'filter' => 'JSMin',
                    ),
                ),
            ),
        );

        $assetFilterManager = new AssetFilterManager($config['filters']);
        $assetCacheManager = $this->getAssetCacheManager();

        $resolver     = $this->getResolver(__DIR__ . '/../../_files/require-jquery.js');
        $assetManager = new AssetManager($resolver, $config);
        $assetManager->setAssetFilterManager($assetFilterManager);
        $assetManager->setAssetCacheManager($assetCacheManager);

        $this->request = new ConsoleRequest();
        $this->routeMatch = $this->createRouteMatch(['controller' => 'console']);

        $this->event = new MvcEvent();
        $this->event->setRouteMatch($this->routeMatch);

        $this->controller = new ConsoleController(
            $this->createMock(AdapterInterface::class),
            $assetManager,
            array()
        );
        $this->controller->setEvent($this->event);
    }

    public function createRouteMatch(array $params = [])
    {
        $class = class_exists(V2RouteMatch::class) ? V2RouteMatch::class : RouteMatch::class;
        return new $class($params);
    }

    /**
     *
     * @return ResolverInterface
     */
    protected function getResolver()
    {
        $mimeResolver = new MimeResolver();
        $resolver = new MapResolver(array(
            self::$assetName => __DIR__ . '/../../_files/require-jquery.js'
        ));
        $resolver->setMimeResolver($mimeResolver);
        return $resolver;
    }

    /**
     * @return AssetCacheManager
     */
    protected function getAssetCacheManager()
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $config = array(
            self::$assetName => array(
                'cache' => 'FilePathCache',
                'options' => array(
                    'dir' => sys_get_temp_dir()
                )
            ),
        );
        $assetCacheManager = new AssetCacheManager($serviceLocator, $config);
        return $assetCacheManager;
    }

    public function testWarmupAction()
    {
        $this->routeMatch->setParam('action', 'warmup');
        $this->controller->dispatch($this->request);

        $dumpedAsset = sys_get_temp_dir() . '/' . self::$assetName;
        $this->assertEquals(
            file_get_contents($dumpedAsset),
            JSMin::minify(file_get_contents(__DIR__ . '/../../_files/require-jquery.js'))
        );
    }
}
