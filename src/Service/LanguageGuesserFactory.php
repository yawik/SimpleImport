<?php
/**
 * YAWIK-SimpleImport
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2019 Cross Solution <http://cross-solution.de>
 */
  
/** */
namespace SimpleImport\Service;

use Interop\Container\ContainerInterface;
use SimpleImport\Options\LanguageGuesserOptions;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Factory for \SimpleImport\Service\LanguageGuesser
 * 
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @todo write test  
 */
class LanguageGuesserFactory implements FactoryInterface
{
    
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $options = $container->get(LanguageGuesserOptions::class);
        $client  = new \Zend\Http\Client($options->getUri());
        $service = new LanguageGuesser($client);
        
        return $service;    
    }
}
