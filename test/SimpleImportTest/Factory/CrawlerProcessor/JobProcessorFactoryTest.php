<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */

namespace SimpleImportTest\Factory\CrawlerProcessor;

use PHPUnit\Framework\TestCase;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use SimpleImport\Factory\CrawlerProcessor\JobProcessorFactory;
use SimpleImport\CrawlerProcessor\JobProcessor;
use SimpleImport\Options\ModuleOptions;
use SimpleImport\Job\GeocodeLocation;
use Jobs\Repository\Job as JobRepository;
use Jobs\Repository\Categories as JobCategoriesRepository;

/**
 * @coversDefaultClass \SimpleImport\Factory\CrawlerProcessor\JobProcessorFactory
 */
class JobProcessorFactoryTest extends TestCase
{

    /**
     * @covers ::__invoke
     */
    public function testInvoke()
    {
        $jobRepository = $this->getMockBuilder(JobRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $jobCategoriesRepository = $this->getMockBuilder(JobCategoriesRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repositories = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $repositories->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValueMap([
                ['Jobs/Job', $jobRepository],
                ['Jobs/Category', $jobCategoriesRepository],
            ]));

        $jobGeocodeLocation = $this->getMockBuilder(GeocodeLocation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $container->expects($this->exactly(3))
            ->method('get')
            ->will($this->returnValueMap([
                ['repositories', $repositories],
                ['SimpleImport/Options/Module', new ModuleOptions()],
                ['SimpleImport/JobGeocodeLocation', $jobGeocodeLocation],
            ]));


        $jobProcessor = (new JobProcessorFactory())->__invoke($container, JobProcessor::class);
        $this->assertInstanceOf(JobProcessor::class, $jobProcessor);
    }
}
