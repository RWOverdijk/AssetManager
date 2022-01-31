<?php

namespace AssetManager\Command;

use AssetManager\Service\AssetManager;
use Laminas\Cli\Command\AbstractParamAwareCommand;
use Laminas\Cli\Input\BoolParam;
use Laminas\Cli\Input\ParamAwareInputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function count;
use function is_dir;
use function is_file;
use function scandir;
use function sprintf;
use function unlink;

class Warmup extends AbstractParamAwareCommand
{
    protected AssetManager $assetManager;
    protected array $appConfig;

    public function __construct(AssetManager $assetManager, array $appConfig)
    {
        parent::__construct();
        $this->assetManager = $assetManager;
        $this->appConfig = $appConfig;
    }

    protected function configure(): void
    {
        $this->setName('warmup');
        $purgeParam = new BoolParam('purge');
        $purgeParam->setShortcut('p');
        $purgeParam->setDefault(false);
        $purgeParam->setDescription('Forces cache flushing');

        $this->addParam($purgeParam);
    }

    /**
     * @param ParamAwareInputInterface $input
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $purge = $input->getParam('purge');
        $verbose = $input->getOption('verbose');

        try {
            $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
            if ($verbose) {
                $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
            }

            if ($purge) {
                $this->purgeCache($output);
            }

            $output->writeln('Collecting all assets...');

            $collection = $this->assetManager->getResolver()->collect();
            $output->writeln(
                sprintf(
                    'Collected %d assets, warming up...',
                    count($collection)
                )
            );

            foreach ($collection as $path) {
                $asset = $this->assetManager->getResolver()->resolve($path);
                $this->assetManager->getAssetFilterManager()->setFilters($path, $asset);
                $this->assetManager->getAssetCacheManager()->setCache($path, $asset)->dump();
            }

            $output->writeln('Warming up finished...');
            return Command::SUCCESS;
        } catch (Throwable $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }
    }

    protected function purgeCache(OutputInterface $output): bool
    {
        if (empty($this->appConfig['asset_manager']['caching'])) {
            return false;
        }

        foreach ($this->appConfig['asset_manager']['caching'] as $configName => $config) {
            if (empty($config['options']['dir'])) {
                continue;
            }

            $output->writeln(
                sprintf(
                    'Purging %s on "%s"...',
                    $config['options']['dir'],
                    $configName
                )
            );

            $node = $config['options']['dir'];

            if ($configName !== 'default') {
                $node .= '/' . $configName;
            }

            $this->recursiveRemove($node, $output);
        }

        return true;
    }

    protected function recursiveRemove(string $node, OutputInterface $output): void
    {
        if (is_dir($node)) {
            $objects = scandir($node);

            foreach ($objects as $object) {
                if ($object === '.' || $object === '..') {
                    continue;
                }
                $this->recursiveRemove($node . '/' . $object, $output);
            }
        } elseif (is_file($node)) {
            $output->writeln(sprintf("unlinking %s...", $node));
            unlink($node);
        }
    }
}
