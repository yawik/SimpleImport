<?php
/**
 * YAWIK
 *
 * @filesource
 * @license    MIT
 * @copyright  2013 - 2017 Cross Solution <http://cross-solution.de>
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */

namespace SimpleImportTest\Job;

use PHPUnit\Framework\TestCase;

use Geocoder\Query\GeocodeQuery;
use SimpleImport\Job\GeocodeLocation;
use Geocoder\Geocoder;
use Geocoder\Model\Address;
use Geocoder\Model\Coordinates;
use Geocoder\Model\AdminLevelCollection;
use Geocoder\Model\AdminLevel;
use Geocoder\Model\Country;
use Geocoder\Model\AddressCollection;
use Jobs\Entity\Location;
use Exception;

/**
 * @coversDefaultClass \SimpleImport\Job\GeocodeLocation
 */
class GeocodeLocationTest extends TestCase
{

    /**
     * @var GeocodeLocation
     */
    private $target;

    /**
     * @var Geocoder
     */
    private $geocoder;

    /**
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->geocoder = $this->getMockBuilder(Geocoder::class)
            ->getMock();

        $this->target = new GeocodeLocation($this->geocoder);
    }

    /**
     * @covers ::__construct()
     * @covers ::getLocations()
     */
    public function testGetLocationsInvalidPlace()
    {
        $this->geocoder->expects($this->once())
            ->method('geocodeQuery')
            ->will($this->throwException(new Exception()));

        $locations = $this->target->getLocations('an invalid place');
        $this->assertInternalType('array', $locations);
        $this->assertEmpty($locations);
    }

    /**
     * @covers ::__construct()
     * @covers ::getLocations()
     * @covers ::createLocationFromAddress()
     */
    public function testGetLocationsValid()
    {
        $address = new Address(
            'test',
            new AdminLevelCollection([new AdminLevel(1, 'Zürich', 'ZU')]),
            new Coordinates(47.29368780000001, 8.5554861),
            null,
            null,
            null,
            '8800',
            'Thalwil',
            null,
            new Country('Schweiz', 'CH')
        );

        $place = 'a valid place';
        $addressCollection = new AddressCollection([$address]);
        $this->geocoder->expects($this->once())
            ->method('geocodeQuery')
            ->with($this->isInstanceOf(GeocodeQuery::class))
            ->willReturn($addressCollection);

        $locations = $this->target->getLocations($place);
        $this->assertInternalType('array', $locations);
        $this->assertCount(1, $locations);

        /** @var Location $location */
        $location = reset($locations);
        $this->assertInstanceOf(Location::class, $location);
        $this->assertSame($address->getPostalCode(), $location->getPostalCode());
        $this->assertSame($address->getLocality(), $location->getCity());
        $this->assertSame($address->getAdminLevels()->first()->getName(), $location->getRegion());
        $this->assertSame($address->getCountry()->getName(), $location->getCountry());

        $coordinates = $location->getCoordinates()->getCoordinates();
        $this->assertSame($address->getCoordinates()->getLatitude(), $coordinates[1]);
        $this->assertSame($address->getCoordinates()->getLongitude(), $coordinates[0]);
    }
}
