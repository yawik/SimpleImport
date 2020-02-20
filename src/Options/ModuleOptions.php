<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\Options;

use Laminas\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
    const SERVICE_ID = 'SimpleImport/Options/Module';

    /**
     * Default delay when to proceed another import run in minutes
     *
     * @var int
     */
    private $importRunDelay = 1440;

    /**
     * Geocode locale
     *
     * @var string
     */
    private $geocodeLocale = 'de';

    /**
     * Geocode region biasing
     *
     * @var string
     */
    private $geocodeRegion = 'DE';

    /**
     * Whether to use an SSL connection for Geocoding
     *
     * @var bool
     */
    private $geocodeUseSsl = true;

    /**
     * An optional Geocode Google map API key
     *
     * @var string
     */
    private $geocodeGoogleApiKey = null;

    /**
     * List of available classifications to import
     *
     * @var array
     */
    private $classifications = [
        'professions',
        'industries',
        'employmentTypes',
    ];

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var int
     */
    private $shuffleInterval = 0;

    /**
     * @return array
     */
    public function getCache(): array
    {
        return $this->cache;
    }

    /**
     * @param array $cache
     */
    public function setCache(array $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return int
     */
    public function getImportRunDelay()
    {
        return $this->importRunDelay;
    }

    /**
     * @param int $importRunDelay
     * @return ModuleOptions
     */
    public function setImportRunDelay($importRunDelay)
    {
        $this->importRunDelay = $importRunDelay;
        return $this;
    }

    /**
     * @return string
     */
    public function getGeocodeLocale()
    {
        return $this->geocodeLocale;
    }

    /**
     * @param string $geocodeLocale
     * @return ModuleOptions
     */
    public function setGeocodeLocale($geocodeLocale)
    {
        $this->geocodeLocale = $geocodeLocale;
        return $this;
    }

    /**
     * @return string
     */
    public function getGeocodeRegion()
    {
        return $this->geocodeRegion;
    }

    /**
     * @param string $geocodeRegion
     * @return ModuleOptions
     */
    public function setGeocodeRegion($geocodeRegion)
    {
        $this->geocodeRegion = $geocodeRegion;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getGeocodeUseSsl()
    {
        return $this->geocodeUseSsl;
    }

    /**
     * @param boolean $geocodeUseSsl
     * @return ModuleOptions
     */
    public function setGeocodeUseSsl($geocodeUseSsl)
    {
        $this->geocodeUseSsl = $geocodeUseSsl;
        return $this;
    }

    /**
     * @return string
     */
    public function getGeocodeGoogleApiKey()
    {
        return $this->geocodeGoogleApiKey;
    }

    /**
     * @param string $geocodeGoogleApiKey
     * @return ModuleOptions
     */
    public function setGeocodeGoogleApiKey($geocodeGoogleApiKey)
    {
        $this->geocodeGoogleApiKey = $geocodeGoogleApiKey;
        return $this;
    }

    /**
     * @return array
     */
    public function getClassifications()
    {
        return $this->classifications;
    }

    /**
     * @param array $classifications
     * @return ModuleOptions
     */
    public function setClassifications($classifications)
    {
        $this->classifications = $classifications;
        return $this;
    }

    /**
     * Get shuffleInterval
     *
     * @return int
     */
    public function getShuffleInterval(): int
    {
        return $this->shuffleInterval;
    }

    /**
     * Set shuffleInterval
     *
     * @param int $shuffleInterval
     */
    public function setShuffleInterval(int $shuffleInterval): void
    {
        $this->shuffleInterval = $shuffleInterval;
    }
}