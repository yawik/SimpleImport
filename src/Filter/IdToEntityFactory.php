<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2018 Cross Solution <http://cross-solution.de>
 */
  
/** */
namespace SimpleImport\Filter;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Factory for \SimpleImport\Filter\IdToEntity
 * 
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @todo write test  
 */
class IdToEntityFactory implements FactoryInterface
{
    /**
     * Creates an IdToEntity filter.
     *
     * Requires the key 'document' in the options array which should be the key to load the
     * entity repository from the repositories plugin manager.
     *
     * Optionally sets the notFoundValue, if the key 'not_found_value' is set in the options array.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return IdToEntity
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (!isset($options['document'])) {
            throw new ServiceNotCreatedException('Missing option "document".');
        }

        $repositories = $container->get('repositories');
        $repository   = $repositories->get($options['document']);

        $service      = new IdToEntity($repository);

        if (isset($options['not_found_value'])) {
            $service->setNotFoundValue($options['not_found_value']);
        }
        
        return $service;    
    }
}
