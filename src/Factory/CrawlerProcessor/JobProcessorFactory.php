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
use Laminas\ServiceManager\Factory\FactoryInterface;
use SimpleImport\CrawlerProcessor\JobProcessor;
use SimpleImport\DataFetch;
use SimpleImport\InputFilter\JobDataInputFilter;
use SimpleImport\Hydrator\JobHydrator;
use SimpleImport\Hydrator\Job\ClassificationsHydrator;
use Core\Form\Hydrator\Strategy\TreeSelectStrategy;
use SimpleImport\Filter\ShufflePublishDateFilter;
use Laminas\Http\Client;
use Symfony\Component\Filesystem\Filesystem;

class JobProcessorFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Laminas\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $client = (new Client())->setOptions(['timeout' => 5]);
        $httpFetch = new DataFetch\HttpFetch($client);
        $jsonFetch = new DataFetch\JsonFetch($httpFetch);
        $plainTextFetch = new DataFetch\PlainTextFetch($httpFetch);
        $repositories = $container->get('repositories');
        $jobRepository = $repositories->get('Jobs/Job');
        $moduleOptions = $container->get('SimpleImport/Options/Module');
        $classificationsHydrator = new ClassificationsHydrator(new TreeSelectStrategy(), $repositories->get('Jobs/Category'), $moduleOptions->getClassifications());
        $jobHydrator = new JobHydrator(
            $container->get('SimpleImport/JobGeocodeLocation'),
            $classificationsHydrator,
            $container->get('FilterManager')->get(ShufflePublishDateFilter::class)
        );
        $dataInputFilter = new JobDataInputFilter($moduleOptions->getClassifications());

        $fs = new Filesystem();

        // identify var dir location
        /* @var \Core\Options\ModuleOptions $options */
        $options = $container->get('Core/Options');
        $lockDir = dirname($options->getCacheDir()).'/simple-import';
        if(!is_dir($lockDir)){
            mkdir($lockDir,0777,true);
        }

        return new JobProcessor(
            $jsonFetch,
            $plainTextFetch,
            $jobRepository,
            $jobHydrator,
            $dataInputFilter,
            $fs,
            $lockDir
        );
    }
}