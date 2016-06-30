<?php

namespace AssetManager\Service;

use AssetManager\Exception;
use AssetManager\Resolver\AggregateResolver;
use AssetManager\Resolver\AggregateResolverAwareInterface;
use AssetManager\Resolver\MimeResolverAwareInterface;
use AssetManager\Resolver\ResolverInterface;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory class for AssetManagerService
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class AggregateResolverServiceFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config         = $container->get('Config');
        $config         = isset($config['asset_manager']) ? $config['asset_manager'] : array();
        $resolver       = new AggregateResolver();

        if (empty($config['resolvers'])) {
            return $resolver;
        }

        foreach ($config['resolvers'] as $resolverService => $priority) {

            $resolverService = $container->get($resolverService);

            if (!$resolverService instanceof ResolverInterface) {
                throw new Exception\RuntimeException(
                    'Service does not implement the required interface ResolverInterface.'
                );
            }

            if ($resolverService instanceof AggregateResolverAwareInterface) {
                $resolverService->setAggregateResolver($resolver);
            }

            if ($resolverService instanceof MimeResolverAwareInterface) {
                $resolverService->setMimeResolver($container->get('AssetManager\Service\MimeResolver'));
            }

            if ($resolverService instanceof AssetFilterManagerAwareInterface) {
                $resolverService->setAssetFilterManager(
                    $container->get('AssetManager\Service\AssetFilterManager')
                );
            }

            $resolver->attach($resolverService, $priority);
        }

        return $resolver;
    }

    /**
     * {@inheritDoc}
     *
     * @return AggregateResolver
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, AggregateResolver::class);
    }
}
