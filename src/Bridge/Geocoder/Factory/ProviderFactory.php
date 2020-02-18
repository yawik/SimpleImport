<?php


namespace SimpleImport\Bridge\Geocoder\Factory;


use Geocoder\Provider\GoogleMaps\GoogleMaps as GoogleMapsProvider;
use GuzzleHttp\Client as GuzzleClient;
use Http\Adapter\Guzzle6\Client as HttpClient;
use Http\Client\Common\PluginClient;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ProviderFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return GoogleMapsProvider|object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var \SimpleImport\Options\ModuleOptions $moduleOptions */
        $moduleOptions = $container->get('SimpleImport/Options/Module');

        $httpAdapter = $this->createHttpAdapter();
        $region = $moduleOptions->getGeocodeRegion();
        $apiKey = $moduleOptions->getGeocodeGoogleApiKey();
        $pluginClient = new PluginClient($httpAdapter);

        $provider = new GoogleMapsProvider(
            $pluginClient,
            $region,
            $apiKey
        );

        return $provider;
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