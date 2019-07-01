<?php


namespace SimpleImportTest\Bridge\Geocoder\Factory;

use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use SimpleImport\Bridge\Geocoder\Factory\ProviderFactory;
use SimpleImport\Options\ModuleOptions;

class ProviderFactoryTest extends TestCase
{
    /**
     * @var ProviderFactory
     */
    private $target;

    protected function setUp(): void
    {
        $this->target = new ProviderFactory();
    }

    public function testInvoke()
    {
        $container = $this->createMock(ContainerInterface::class);
        $moduleOptions = $this->createMock(ModuleOptions::class);
        $container->expects($this->once())
            ->method('get')
            ->with('SimpleImport/Options/Module')
            ->willReturn($moduleOptions)
        ;

        $ob = $this->target->__invoke($container, 'some-name');

        $this->assertInstanceOf(GoogleMaps::class, $ob);
    }
}