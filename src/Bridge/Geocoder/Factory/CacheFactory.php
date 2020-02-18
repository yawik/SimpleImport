<?php

declare(strict_types=1);

namespace SimpleImport\Bridge\Geocoder\Factory;


use Interop\Container\ContainerInterface;
use SimpleImport\Options\ModuleOptions;
use Laminas\Cache\Psr\SimpleCache\SimpleCacheDecorator;
use Laminas\Cache\StorageFactory;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CacheFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var \SimpleImport\Options\ModuleOptions $options */
        $options = $container->get(ModuleOptions::SERVICE_ID);
        $config = $options->getCache();

        if('filesystem' === $config['adapter']['name']){
            $cacheDir = $config['adapter']['options']['cacheDir'];
            if(!is_dir($cacheDir)){
                mkdir($cacheDir, 0777, true);
            }
        }
        $storage = StorageFactory::factory($config);
        $cache = new SimpleCacheDecorator($storage);

        return $cache;
    }
}