<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\Factory\Job;


use GuzzleHttp\Client as GuzzleClient;
use Http\Adapter\Guzzle6\Client as HttpClient;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Geocoder\Provider\GoogleMaps\GoogleMaps as GoogleMapsProvider;
use SimpleImport\Job\GeocodeLocation;

class GeocodeLocationFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var \SimpleImport\Options\ModuleOptions $moduleOptions */
        $moduleOptions = $container->get('SimpleImport/Options/Module');

        $client = $this->createHttpAdapter();
        $region = $moduleOptions->getGeocodeRegion();
        $apiKey = $moduleOptions->getGeocodeGoogleApiKey();
        $geocoder = new GoogleMapsProvider(
            $client,
            $region,
            $apiKey
        );
        
        return new GeocodeLocation($geocoder);
    }

    private function createHttpAdapter()
    {
        $config = [
            'timeout' => 2.0,
            'verify' => false,
        ];

        $guzzle = new GuzzleClient($config);
        $adapter = new HttpClient($guzzle);

        return $adapter;
    }
}