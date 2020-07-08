<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */

namespace SimpleImportTest\Factory\CrawlerProcessor;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use SimpleImport\Factory\CrawlerProcessor\JobProcessorFactory;
use SimpleImport\CrawlerProcessor\JobProcessor;
use SimpleImport\Options\ModuleOptions;
use SimpleImport\Job\GeocodeLocation;
use Jobs\Repository\Job as JobRepository;
use Jobs\Repository\Categories as JobCategoriesRepository;
use SimpleImport\Filter\ShufflePublishDateFilter;
use Core\Options\ModuleOptions as CoreOptions;

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

        $shuffleFilter = $this->getMockBuilder(ShufflePublishDateFilter::class)->disableOriginalConstructor()->getMock();
        $filterManager = $this->getMockBuilder(ContainerInterface::class)
                ->getMock();
        $filterManager->expects($this->once())->method('get')->with(ShufflePublishDateFilter::class)
            ->willReturn($shuffleFilter);

        $coreOptions = $this->getMockBuilder(CoreOptions::class)
            ->disableOriginalConstructor()
            ->getMock();
        $coreOptions->expects($this->once())
            ->method('getLogDir')
            ->willReturn(__DIR__.'/../../../sandbox/var/log');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $container->expects($this->exactly(5))
            ->method('get')
            ->will($this->returnValueMap([
                ['repositories', $repositories],
                ['SimpleImport/Options/Module', new ModuleOptions()],
                ['SimpleImport/JobGeocodeLocation', $jobGeocodeLocation],
                ['FilterManager', $filterManager],
                ['Core/Options',$coreOptions]
            ]));

        $jobProcessor = (new JobProcessorFactory())->__invoke($container, JobProcessor::class);
        $this->assertInstanceOf(JobProcessor::class, $jobProcessor);
    }
}
