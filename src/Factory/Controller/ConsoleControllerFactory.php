<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\Factory\Controller;


use SimpleImport\Controller\ConsoleController;
use SimpleImport\InputFilter\CrawlerInputFilter;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ConsoleControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return ConsoleController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $crawlerRepository = $container->get('repositories')->get('SimpleImport/Crawler');
        $crawlerProcessors = $container->get('SimpleImport/CrawlerProcessorManager');
        $crawlerInputFilter = new CrawlerInputFilter();
        $moduleOptions = $container->get('SimpleImport/Options/Module');
        $logger = $container->get('SimpleImport/Log');
        
        return new ConsoleController($crawlerRepository, $crawlerProcessors, $crawlerInputFilter, $moduleOptions, $logger);
    }
}