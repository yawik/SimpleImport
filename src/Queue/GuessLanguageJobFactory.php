<?php
/**
 * YAWIK-SimpleImport
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2019 Cross Solution <http://cross-solution.de>
 */
  
/** */
namespace SimpleImport\Queue;

use Interop\Container\ContainerInterface;
use SimpleImport\Service\LanguageGuesser;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Factory for \SimpleImport\Queue\GuessLanguageJob
 * 
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @todo write test  
 */
class GuessLanguageJobFactory implements FactoryInterface
{
    
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $service = new GuessLanguageJob(
            $container->get('repositories')->get('Jobs'),
            $container->get(LanguageGuesser::class)
        );
        
        return $service;    
    }
}
