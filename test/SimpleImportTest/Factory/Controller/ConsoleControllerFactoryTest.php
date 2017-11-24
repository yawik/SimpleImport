<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */

namespace SimpleImportTest\Factory\Controller;

use Interop\Container\ContainerInterface;
use SimpleImport\Factory\Controller\ConsoleControllerFactory;
use SimpleImport\Controller\ConsoleController;
use SimpleImport\Repository\Crawler as CrawlerRepository;
use SimpleImport\CrawlerProcessor\Manager as CrawlerProcessorManager;
use SimpleImport\InputFilter\CrawlerInputFilter;
use SimpleImport\Options\ModuleOptions;
use Zend\Log\LoggerInterface;

/**
 * @coversDefaultClass \SimpleImport\Factory\Controller\ConsoleControllerFactory
 */
class ConsoleControllerFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers ::__invoke
     */
    public function testInvoke()
    {
        $crawlerRepository = $this->getMockBuilder(CrawlerRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repositories = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $repositories->expects($this->once())
            ->method('get')
            ->with($this->equalTo('SimpleImport/Crawler'))
            ->willReturn($crawlerRepository);

        $crawlerInputFilter = $this->getMockBuilder(CrawlerInputFilter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $InputFilterManager = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $InputFilterManager->expects($this->once())
            ->method('get')
            ->with($this->equalTo(CrawlerInputFilter::class))
            ->willReturn($crawlerInputFilter);

        $crawlerProcessorManager = $this->getMockBuilder(CrawlerProcessorManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $container->expects($this->exactly(5))
            ->method('get')
            ->will($this->returnValueMap([
                ['repositories', $repositories],
                ['SimpleImport/CrawlerProcessorManager', $crawlerProcessorManager],
                ['InputFilterManager', $InputFilterManager],
                ['SimpleImport/Options/Module', new ModuleOptions()],
                ['SimpleImport/Log', $logger],
            ]));


        $controller = (new ConsoleControllerFactory())->__invoke($container, ConsoleController::class);
        $this->assertInstanceOf(ConsoleController::class, $controller);
    }
}
