<?php

namespace AssetManager\Controller;

use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\Request as ConsoleRequest;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;

use AssetManager\Service\AssetManager;

/**
 * Class ConsoleController
 *
 * @package AssetManager\Controller
 */
class ConsoleController extends AbstractActionController
{

    /**
     * @var \Zend\Console\Adapter\AdapterInterface console object
     */
    protected $console;

    /**
     * @var \AssetManager\Service\AssetManager asset manager object
     */
    protected $assetManager;

    /**
     * @var array associative array represents app config
     */
    protected $appConfig;

    /**
     * @param Console $console
     * @param AssetManager $assetManager
     * @param array $appConfig
     */
    public function __construct(Console $console, AssetManager $assetManager, array $appConfig)
    {
        $this->console      = $console;
        $this->assetManager = $assetManager;
        $this->appConfig    = $appConfig;
    }

    /**
     * {@inheritdoc}
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return mixed|ResponseInterface
     * @throws \RuntimeException
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response = null)
    {
        if (!($request instanceof ConsoleRequest)) {
            throw new \RuntimeException('You can use this controller only from a console!');
        }

        return parent::dispatch($request, $response);
    }

    /**
     * Dumps all assets to cache directories.
     */
    public function warmupAction()
    {
        $request    = $this->getRequest();
        $purge      = $request->getParam('purge', false);

        // purge cache for every configuration
        if ($purge && !empty($this->appConfig['asset_manager']['caching'])) {
            $this->purgeCache();
        }

        $this->console->writeLine('Collecting all assets...');
        $collection = $this->assetManager->getResolver()->collect();

        $this->console->writeLine(sprintf('Collected %d assets, warming up', count($collection)));
        foreach ($collection as $path) {
            $asset = $this->assetManager->getResolver()->resolve($path);
            $this->assetManager->getAssetCacheManager()->setCache($path, $asset)->dump();
        }
    }

    /**
     * Purges all directories defined as AssetManager cache dir.
     */
    protected function purgeCache()
    {

        foreach ($this->appConfig['asset_manager']['caching'] as $configName => $config) {
            if (empty($config['options']['dir'])) {
                continue;
            }
            $this->console->writeLine(sprintf('Purging %s on "%s"...', $config['options']['dir'], $configName));
            self::recursiveRemove($config['options']['dir']);
        }
    }

    /**
     * Removes given node from filesystem (recursively).
     * @param $node string - uri of node that should be removed from filesystem
     */
    protected function recursiveRemove($node)
    {

        if (is_dir($node)) {
            $objects = scandir($node);

            foreach ($objects as $object) {
                if ($object === '.' || $object === '..') {
                    continue;
                }
                $this->recursiveRemove($node . '/' . $object);
            }

            rmdir($node);
        } else {
            unlink($node);
        }
    }
}
