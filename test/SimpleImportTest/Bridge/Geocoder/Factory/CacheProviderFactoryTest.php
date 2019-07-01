<?php

namespace SimpleImportTest\Bridge\Geocoder\Factory;

use Geocoder\Provider\Cache\ProviderCache;
use Geocoder\Provider\Provider;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use SimpleImport\Bridge\Geocoder\Factory\CacheProviderFactory;

class CacheProviderFactoryTest extends TestCase
{
    public function testInvoke()
    {
        $cache = $this->createMock(CacheInterface::class);
        $realProvider = $this->createMock(Provider::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())
            ->method('get')
            ->willReturnMap([
                ['SimpleImport/Geocoder/Provider',$realProvider],
                ['SimpleImport/Geocoder/Cache',$cache]
            ]);

        $factory = new CacheProviderFactory();
        $provider = $factory($container,'some-name');

        $this->assertInstanceOf(ProviderCache::class, $provider);
    }
}
