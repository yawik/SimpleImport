<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\Factory\CrawlerProcessor;


use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use SimpleImport\RemoteFetch\JsonRemoteFetch;
use SimpleImport\InputFilter\JobDataInputFilter;
use SimpleImport\Hydrator\JobHydrator;
use Zend\Http\Client;

class JobProcessorFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $jsonFetch = new JsonRemoteFetch(new Client());
        $jobRepository = $container->get('repositories')->get('Jobs/Job');
        $jobHydrator = new JobHydrator();
        $dataInputFilter = new JobDataInputFilter();
        
        return new \SimpleImport\CrawlerProcessor\JobProcessor($jsonFetch, $jobRepository, $jobHydrator, $dataInputFilter);
    }
}