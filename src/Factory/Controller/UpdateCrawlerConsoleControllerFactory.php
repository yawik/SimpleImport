<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2018 Cross Solution <http://cross-solution.de>
 */
  
/** */
namespace SimpleImport\Factory\Controller;

use SimpleImport\Controller\UpdateCrawlerConsoleController;
use Interop\Container\ContainerInterface;
use SimpleImport\InputFilter\CrawlerInputFilter;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Factory for \SimpleImport\Controller\UpdateCrawlerConsoleController
 * 
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @todo write test  
 */
class UpdateCrawlerConsoleControllerFactory implements FactoryInterface
{

    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return UpdateCrawlerConsoleController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var CrawlerInputFilter $filter
         * @var \Zend\Router\RouteMatch $routeMatch
         */

        $controller  = new UpdateCrawlerConsoleController();
        $application = $container->get('Application');
        $routeMatch  = $application->getMvcEvent()->getRouteMatch();

        if ('index' != $routeMatch->getParam('action')) {
            $filters = $container->get('InputFilterManager');
            $filter  = $filters->get(CrawlerInputFilter::class);

            $controller->setInputFilter($filter);
        }

        return $controller;
    }
}
