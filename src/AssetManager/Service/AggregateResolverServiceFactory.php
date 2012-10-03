<?php

namespace AssetManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use AssetManager\Resolver\AggregateResolver;
use AssetManager\Exception;
use AssetManager\Resolver\AggregateResolverAwareInterface;
use AssetManager\Resolver\MimeResolverAwareInterface;
use AssetManager\Resolver\ResolverInterface;

/**
 * Factory class for AssetManagerService
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class AggregateResolverServiceFactory implements FactoryInterface
{

    /**
     * {@inheritDoc}
     *
     * @return AggregateResolver
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config         = $serviceLocator->get('Config');
        $config         = isset($config['asset_manager']) ? $config['asset_manager'] : array();
        $resolver       = new AggregateResolver();

        if (empty($config['resolvers'])) {
            return $resolver;
        }

        foreach ($config['resolvers'] as $resolverService => $priority) {

            $resolverService = $serviceLocator->get($resolverService);

            if (!$resolverService instanceof ResolverInterface) {
                throw new Exception\RuntimeException(
                    'Service does not implement the required interface ResolverInterface.'
                );
            }

            if ($resolverService instanceof AggregateResolverAwareInterface) {
                $resolverService->setAggregateResolver($resolver);
            }

            if ($resolverService instanceof MimeResolverAwareInterface) {
                $resolverService->setMimeResolver($serviceLocator->get('mime_resolver'));
            }

            if ($resolverService instanceof AssetFilterManagerAwareInterface) {
                $resolverService->setAssetFilterManager(
                    $serviceLocator->get('AssetManager\Service\AssetFilterManager')
                );
            }

            $resolver->attach($resolverService, $priority);
        }

        return $resolver;
    }
}
