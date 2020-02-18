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
use Laminas\ServiceManager\Factory\FactoryInterface;
use SimpleImport\Job\GeocodeLocation;

class GeocodeLocationFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Laminas\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var \SimpleImport\Options\ModuleOptions $options */
        $options = $container->get('SimpleImport/Options/Module');
        $locale = $options->getGeocodeLocale();
        $cacheProvider = $container->get('SimpleImport/Geocoder/CacheProvider');
        $logger = $container->get('SimpleImport/Log');
        $service = new GeocodeLocation($cacheProvider, $locale);
        $service->setLogger($logger);

        return $service;
    }
}