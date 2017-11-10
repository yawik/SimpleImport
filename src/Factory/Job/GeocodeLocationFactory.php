<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\Factory\Job;


use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Ivory\HttpAdapter\CurlHttpAdapter;
use Geocoder\Provider\GoogleMaps as GoogleMapsProvider;
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
        
        $geocoder = new GoogleMapsProvider(
            new CurlHttpAdapter(),
            $moduleOptions->getGeocodeLocale(),
            $moduleOptions->getGeocodeRegion(),
            $moduleOptions->getGeocodeUseSsl(),
            $moduleOptions->getGeocodeGoogleApiKey());
        
        return new GeocodeLocation($geocoder);
    }
}