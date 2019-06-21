<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2018 Cross Solution <http://cross-solution.de>
 */

/** */
namespace SimpleImportTest\Controller;

use Cross\TestUtils\TestCase\SetupTargetTrait;
use Cross\TestUtils\TestCase\ContainerDoubleTrait;
use Cross\TestUtils\TestCase\TestInheritanceTrait;

use Prophecy\Argument;

use SimpleImport\Controller\DeleteCrawlerConsoleController;
use Zend\Console\ColorInterface;
use Zend\Mvc\Console\Controller\AbstractConsoleController;

use Zend\Mvc\Controller\PluginManager;
use PHPUnit\Framework\TestCase;

/**
 * Tests for \SimpleImport\Controller\DeleteCrawlerConsoleController
 *
 * @covers \SimpleImport\Controller\DeleteCrawlerConsoleController
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 *
 */
class DeleteCrawlerConsoleControllerTest extends TestCase
{
    use SetupTargetTrait, TestInheritanceTrait, ContainerDoubleTrait;

    /**
     *
     *
     * @var array|DeleteCrawlerConsoleController|\PHPUnit_Framework_MockObject_MockObject
     */
    private $target = [
        'create' => [
            [
                'for' => ['testInheritance'],
                'reflection' => DeleteCrawlerConsoleController::class,
            ]
        ]
    ];
    //     DeleteCrawlerConsoleController::class,
    //     'getTargetArgs',
    //     'mock' => [ 'siLoadCrawler' ],
    //     '@testInheritance' => ['as_reflection' => true],
    // ];

    private $inheritance = [ AbstractConsoleController::class ];

    /**
     *
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     *
     */
    private $crawlerRepo;

    /**
     *
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $jobRepo;

    private function initTarget()
    {
        $this->crawlerRepo = $this->getMockBuilder(\SimpleImport\Repository\Crawler::class)
                                  ->disableOriginalConstructor()
                                  ->setMethods(['remove'])
                                  ->getMock();
        $this->jobRepo     = $this->getMockBuilder(\Jobs\Repository\Job::class)
                                  ->disableOriginalConstructor()
                                  ->setMethods(['find'])
                                  ->getMock();

        return new DeleteCrawlerConsoleController($this->crawlerRepo, $this->jobRepo);
    }

    public function testDeletingCrawler()
    {
        $name = 'TestCrawler';
        $id   = 'TestId';

        $crawler = $this->getMockBuilder(\SimpleImport\Entity\Crawler::class)->disableOriginalConstructor()
            ->setMethods(['getItems', 'getName', 'getId'])->getMock();


        /* @var \PHPUnit_Framework_MockObject_MockObject|\Zend\Console\Adapter\AdapterInterface $console */
        $console = $this->getMockBuilder(\Zend\Console\Adapter\AdapterInterface::class)
                        ->getMock();

        $crawler->expects($this->once())->method('getName')->willReturn($name);
        $crawler->expects($this->once())->method('getId')->willReturn($id);
        $console->expects($this->once())->method('writeLine')->with(
            sprintf('Crawler "%s" (%s) deleted.', $name, $id),
            ColorInterface::GREEN
        );

        $plugins = $this->createContainerProphecy(
            ['siLoadCrawler' => [$crawler, 1]],
            ['target' => PluginManager::class, 'args_get' => [null]]
        );
        $plugins->setController($this->target)->shouldBeCalled();
        $plugins = $plugins->reveal();

        $item = $this->getMockBuilder(\SimpleImport\Entity\Item::class)->disableOriginalConstructor()
            ->setMethods(['getDocumentId'])->getMock();
        $item->expects($this->once())->method('getDocumentId')->willReturn('jobId');

        $item2 = $this->getMockBuilder(\SimpleImport\Entity\Item::class)->disableOriginalConstructor()
                      ->setMethods(['getDocumentId'])->getMock();
        $item2->expects($this->once())->method('getDocumentId')->willReturn('jobId2');

        $items = [ $item, $item2 ];

        $crawler->expects($this->once())->method('getItems')->willReturn($items);

        $job = $this->getMockBuilder(\Jobs\Entity\Job::class)->disableOriginalConstructor()->setMethods(['delete'])->getMock();
        $job->expects($this->once())->method('delete');

        $this->jobRepo->expects($this->exactly(2))->method('find')->withConsecutive(['jobId'], ['jobId2'])
                                                                  ->will($this->onConsecutiveCalls($job, null));

        $this->crawlerRepo->expects($this->once())->method('remove')->with($crawler);


        $this->target->setPluginManager($plugins);
        $this->target->setConsole($console);

        $this->target->indexAction();
    }
}
