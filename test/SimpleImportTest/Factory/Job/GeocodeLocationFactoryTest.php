<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */

namespace SimpleImportTest\Factory\Job;

use Geocoder\Provider\Provider;
use PHPUnit\Framework\TestCase;

use Interop\Container\ContainerInterface;
use SimpleImport\Factory\Job\GeocodeLocationFactory;
use SimpleImport\Options\ModuleOptions;
use SimpleImport\Job\GeocodeLocation;
use Laminas\Log\LoggerInterface;

/**
 * @coversDefaultClass \SimpleImport\Factory\Job\GeocodeLocationFactory
 */
class GeocodeLocationFactoryTest extends TestCase
{

    /**
     * @covers ::__invoke
     */
    public function testInvoke()
    {
        $provider = $this->createMock(Provider::class);
        $options = $this->createMock(ModuleOptions::class);
        $logger = $this->createMock(LoggerInterface::class);

        $options->expects($this->once())
            ->method('getGeocodeLocale')
            ->willReturn('de');

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap([
                ['SimpleImport/Geocoder/CacheProvider', $provider],
                ['SimpleImport/Options/Module', $options],
                ['SimpleImport/Log', $logger]
            ]));

        $geocodeLocation = (new GeocodeLocationFactory())->__invoke($container, GeocodeLocation::class);
        $this->assertInstanceOf(GeocodeLocation::class, $geocodeLocation);
    }
}
