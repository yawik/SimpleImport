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

class GeocodeLocation
{
    
    /**
     * @var GeoCoderProvider
     */
    private $geocoder;
    
    /**
     * @param GeoCoderProvider $geocoder
     */
    public function __construct(GeoCoderProvider $geocoder)
    {
        $this->geocoder = $geocoder;
    }

    /**
     * @param string $address
     * @return Location[] Job locations
     */
    public function getLocations($address)
    {
        $locations = [];
        $geoCoder = $this->geocoder;

        try {
            $query = GeocodeQuery::create($address);
            $addresses = $geoCoder->geocodeQuery($query);
        } catch (Exception $e) {
            return $locations;
        }
        
        /** @var Address $address */
        foreach ($addresses as $address) {
            $locations[] = $this->createLocationFromAddress($address);
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
        
        $region = $address->getAdminLevels()->first();
        
        if ($region) {
            $location->setRegion($region->getName());
        }
        
        $coordinates = $address->getCoordinates();
        
        if ($coordinates) {
            $point = new Point([$coordinates->getLongitude(), $coordinates->getLatitude()]);
            $location->setCoordinates($point);
        }
        
        return $location;
    }
}
