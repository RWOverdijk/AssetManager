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
        $verbose    = $request->getParam('verbose', false) || $request->getParam('v', false);

        // purge cache for every configuration
        if ($purge) {
            $this->purgeCache($verbose);
        }

        $this->output('Collecting all assets...', $verbose);

        $collection = $this->assetManager->getResolver()->collect();
        $this->output(sprintf('Collected %d assets, warming up...', count($collection)), $verbose);

        foreach ($collection as $path) {
            $asset = $this->assetManager->getResolver()->resolve($path);
            $this->assetManager->getAssetCacheManager()->setCache($path, $asset)->dump();
        }

        $this->output(sprintf('Warming up finished...', $verbose));
    }

    /**
     * Purges all directories defined as AssetManager cache dir.
     * @param bool $verbose verbose flag, default false
     * @return bool false if caching is not set, otherwise true
     */
    protected function purgeCache($verbose = false)
    {

        if (empty($this->appConfig['asset_manager']['caching'])) {
            return false;
        }

        foreach ($this->appConfig['asset_manager']['caching'] as $configName => $config) {

            if (empty($config['options']['dir'])) {
                continue;
            }
            $this->output(sprintf('Purging %s on "%s"...', $config['options']['dir'], $configName), $verbose);

            $node = $config['options']['dir'];

            if ($configName !== 'default') {
                $node .= '/'.$configName;
            }

            $this->recursiveRemove($node, $verbose);
        }

        return true;
    }

    /**
     * Removes given node from filesystem (recursively).
     * @param string $node - uri of node that should be removed from filesystem
     * @param bool $verbose verbose flag, default false
     */
    protected function recursiveRemove($node, $verbose = false)
    {
        if (is_dir($node)) {
            $objects = scandir($node);

            foreach ($objects as $object) {
                if ($object === '.' || $object === '..') {
                    continue;
                }
                $this->recursiveRemove($node . '/' . $object);
            }
        } elseif (is_file($node)) {
            $this->output(sprintf("unlinking %s...", $node), $verbose);
            unlink($node);
        }
    }

    /**
     * Outputs given $line if $verbose i truthy value.
     * @param $line
     * @param bool $verbose verbose flag, default true
     */
    protected function output($line, $verbose = true)
    {
        if ($verbose) {
            $this->console->writeLine($line);
        }
    }
}
