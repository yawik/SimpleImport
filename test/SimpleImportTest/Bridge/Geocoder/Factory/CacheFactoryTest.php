<?php

namespace SimpleImportTest\Bridge\Geocoder\Factory;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use SimpleImport\Bridge\Geocoder\Factory\CacheFactory;
use SimpleImport\Options\ModuleOptions;
use Laminas\Cache\Psr\SimpleCache\SimpleCacheDecorator;

class CacheFactoryTest extends TestCase
{
    public function testInvoke()
    {
        $config = [
            'adapter' => [
                'name' => 'filesystem',
                'options' => [
                    'cacheDir' => sys_get_temp_dir().'/yawik/cache'
                ]
            ],
            'plugins' => ['serializer']
        ];

        $options = $this->createMock(ModuleOptions::class);
        $options->expects($this->once())
            ->method('getCache')
            ->willReturn($config);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())
            ->method('get')
            ->willReturnMap([
                ['SimpleImport/Options/Module', $options],
            ]);


        $factory = new CacheFactory();
        $cache = $factory($container,'some-name');

        $this->assertInstanceOf(SimpleCacheDecorator::class, $cache);
    }
}
