<?php

namespace SimpleImportTest\Bridge\Geocoder\Factory;

use Geocoder\Provider\Cache\ProviderCache;
use Geocoder\Provider\Provider;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use SimpleImport\Bridge\Geocoder\Factory\CacheProviderFactory;
use SimpleImport\Options\ModuleOptions;

class CacheProviderFactoryTest extends TestCase
{
    public function testInvoke()
    {
        $moduleOptions = $this->createMock(ModuleOptions::class);
        $moduleOptions->expects($this->once())
            ->method('getCache')
            ->willReturn([]);

        $cache = $this->createMock(CacheInterface::class);
        $realProvider = $this->createMock(Provider::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [ModuleOptions::SERVICE_ID,$moduleOptions],
                ['SimpleImport/Geocoder/Provider',$realProvider],
                ['SimpleImport/Geocoder/Cache',$cache]
            ]);

        $factory = new CacheProviderFactory();
        $provider = $factory($container,'some-name');

        $this->assertInstanceOf(ProviderCache::class, $provider);
    }
}
