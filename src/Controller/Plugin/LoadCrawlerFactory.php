<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2018 Cross Solution <http://cross-solution.de>
 */
  
/** */
namespace SimpleImport\Controller\Plugin;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Factory for \SimpleImport\Controller\Plugin\LoadCrawler
 * 
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 */
class LoadCrawlerFactory implements FactoryInterface
{

    /**
     * Creates an instance of LoadCrawler plugin.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return LoadCrawler
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $repositories = $container->get('repositories');
        $repository   = $repositories->get('SimpleImport/Crawler');
        $service      = new LoadCrawler($repository);
        
        return $service;    
    }
}
