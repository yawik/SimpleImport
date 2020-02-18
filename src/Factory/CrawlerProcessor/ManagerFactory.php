<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\Factory\CrawlerProcessor;


use SimpleImport\CrawlerProcessor\Manager;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ManagerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Laminas\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new Manager($container, $container->get('Config')['simple_import_crawler_processor_manager']);
    }
}