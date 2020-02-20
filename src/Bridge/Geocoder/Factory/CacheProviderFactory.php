<?php


namespace SimpleImport\Bridge\Geocoder\Factory;


use Geocoder\Provider\Cache\ProviderCache;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CacheProviderFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $cache = $container->get('SimpleImport/Geocoder/Cache');
        $realProvider = $container->get('SimpleImport/Geocoder/Provider');

        return new ProviderCache($realProvider, $cache);
    }
}