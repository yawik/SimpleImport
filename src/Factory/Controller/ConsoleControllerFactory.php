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
use SimpleImport\CrawlerProcessor\Result;
use SimpleImport\Factory\ProgressBarFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

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
        $crawlerInputFilter = $container->get('InputFilterManager')->get(CrawlerInputFilter::class);
        $moduleOptions = $container->get('SimpleImport/Options/Module');
        $logger = $container->get('SimpleImport/Log');
        $resultPrototype = new Result(new ProgressBarFactory());
        
        return new ConsoleController(
            $crawlerRepository, $crawlerProcessors, $crawlerInputFilter, $moduleOptions, $logger, $resultPrototype);
    }
}