<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\Factory\Controller;


use SimpleImport\Controller\DeleteCrawlerConsoleController;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DeleteCrawlerConsoleControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return DeleteCrawlerConsoleController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $repositories      = $container->get('repositories');
        $crawlerRepository = $repositories->get('SimpleImport/Crawler');
        $jobRepository     = $repositories->get('Jobs');

        return new DeleteCrawlerConsoleController($crawlerRepository, $jobRepository);
    }
}
