<?php

namespace AssetManager\Controller;

use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Request as ConsoleRequest;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;

use AssetManager\Service\AssetManager;

class ConsoleController extends AbstractActionController
{
    protected $console;
    protected $assetManager;

    public function __construct(Console $console, AssetManager $assetManager)
    {
        $this->console      = $console;
        $this->assetManager = $assetManager;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response = null)
    {
        if (!($request instanceof ConsoleRequest)) {
            throw new \RuntimeException('You can use this controller only from a console!');
        }
        return parent::dispatch($request, $response);
    }

    public function warmupAction()
    {
        $request = $this->getRequest();
        $purge = $request->getParam('purge', false);
        $appConfig = $this->getServiceLocator()->get('config');

        // purge cache for every configuration
        if ($purge && isset($appConfig['asset_manager']) && isset($appConfig['asset_manager']['caching'])) {

            foreach ($appConfig['asset_manager']['caching'] as $configName => $config) {
                if (isset($config['options']) && isset($config['options']['dir'])) {
                    $this->console->writeLine(sprintf('Purging %s on "%s"...', $config['options']['dir'], $configName));
                    self::rRmDir($config['options']['dir']);
                }
            }
        }

        $this->console->writeLine('Collecting all assets...');
        $collection = $this->assetManager->getResolver()->collect();

        $this->console->writeLine(sprintf('Collected %d assets, warming up', count($collection)));
        foreach ($collection as $path) {
            $asset = $this->assetManager->getResolver()->resolve($path);
            $this->assetManager->getAssetCacheManager()->setCache($path, $asset)->dump();
        }
    }

    private static function rRmDir($dir) {

        if (is_dir($dir)) {
            $objects = scandir($dir);

            foreach ($objects as $object) {

                $path = $dir . '/' . $object;
                if ($object != "." && $object != "..") {
                    if (is_dir($path)) {
                        self::rRmDir($path);
                    } else {
                        unlink($path);
                    }
                }
            }

            rmdir($dir);
        }
    }
}
