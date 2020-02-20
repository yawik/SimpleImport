<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\Job;

use Geo\Entity\Geometry\Point;
use Geocoder\Model\Address;
use Geocoder\Provider\Provider as GeoCoderProvider;
use Geocoder\Query\GeocodeQuery;
use Jobs\Entity\Location;
use Exception;
use Laminas\Log\LoggerAwareTrait;
use Laminas\Log\LoggerAwareInterface;
use Laminas\Log\LoggerInterface;

class GeocodeLocation implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var GeoCoderProvider
     */
    private $geocoder;

    /**
     * @var string
     */
    private $locale;

    /**
     * GeocodeLocation constructor.
     * @param GeoCoderProvider $geocoder
     * @param string $locale
     */
    public function __construct(GeoCoderProvider $geocoder, string $locale)
    {
        $this->geocoder = $geocoder;
        $this->locale = $locale;
    }

    public function getLogger()
    {
        if (!$this->logger) {
            $this->setLogger(new class implements LoggerInterface
            {
                public function emerg($message, $extra = []) {}
                public function alert($message, $extra = []) {}
                public function crit($message, $extra = []) {}
                public function err($message, $extra = []) {}
                public function warn($message, $extra = []) {}
                public function notice($message, $extra = []) {}
                public function info($message, $extra = []) {}
                public function debug($message, $extra = []) {}
            });
        }

        return $this->logger;
    }

    /**
     * @param   string $address
     * @return  array
     * @throws  \Geocoder\Exception\Exception
     */
    public function getLocations(string $address)
    {
        $locations = [];
        $geoCoder = $this->geocoder;
        $locale = $this->locale;

        try {
            $query = GeocodeQuery::create($address)->withLocale($locale);
            $addresses = $geoCoder->geocodeQuery($query);
        } catch (Exception $e) {
            $this->getLogger()->err('Failed to fetch locations: ' . $e->getMessage());
            $this->getLogger()->debug($e);
            return $locations;
        }

        /** @var Address $address */
        foreach ($addresses as $address) {
            try{
                $locations[] = $this->createLocationFromAddress($address);
            }catch (\Exception $e){
                $this->getLogger()->err('Failed to create locations: ' . $e->getMessage());
                $this->getLogger()->debug($e);
            }
        }

        return $locations;
    }

    /**
     * @param Address $address
     * @return Location
     */
    private function createLocationFromAddress(Address $address)
    {
        $location = new Location();

        $country = $address->getCountry();

        if ($country) {
            $location->setCountry($country->getName());
        }

        $city = $address->getLocality();

        if ($city) {
            $location->setCity($city);
        }

        $postalCode = $address->getPostalCode();

        if ($postalCode) {
            $location->setPostalCode($postalCode);
        }

        if(!empty($address->getAdminLevels())){
            $region = $address->getAdminLevels()->first();
            if ($region) {
                $location->setRegion($region->getName());
            }
        }

        $coordinates = $address->getCoordinates();

        if ($coordinates) {
            $point = new Point([$coordinates->getLongitude(), $coordinates->getLatitude()]);
            $location->setCoordinates($point);
        }

        return $location;
    }
}
