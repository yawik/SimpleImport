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

use CoreTestUtils\TestCase\TestInheritanceTrait;
use SimpleImport\Controller\DeleteCrawlerConsoleController;
use Zend\Console\ColorInterface;
use Zend\Mvc\Console\Controller\AbstractConsoleController;
use Zend\Mvc\Console\View\ViewModel;

/**
 * Tests for \SimpleImport\Controller\DeleteCrawlerConsoleController
 * 
 * @covers \SimpleImport\Controller\DeleteCrawlerConsoleController
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 *  
 */
class DeleteCrawlerConsoleControllerTest extends \PHPUnit_Framework_TestCase
{
    use TestInheritanceTrait;

    /**
     *
     *
     * @var array|DeleteCrawlerConsoleController|\PHPUnit_Framework_MockObject_MockObject
     */
    private $target = [
        DeleteCrawlerConsoleController::class,
        'getTargetArgs',
        'mock' => [ 'params' ],
        '@testInheritance' => ['as_reflection' => true],
    ];

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

    private function getTargetArgs()
    {
        $this->crawlerRepo = $this->getMockBuilder(\SimpleImport\Repository\Crawler::class)
                                  ->disableOriginalConstructor()
                                  ->setMethods(['find', 'findOneByName', 'remove'])
                                  ->getMock();
        $this->jobRepo     = $this->getMockBuilder(\Jobs\Repository\Job::class)
                                  ->disableOriginalConstructor()
                                  ->setMethods(['find'])
                                  ->getMock();

        return [ $this->crawlerRepo, $this->jobRepo ];
    }


    public function testBailsOnNonExistentCrawlers()
    {
        $this->target->expects($this->exactly(4))->method('params')
                     ->withConsecutive(['name'], ['id'], ['name'], ['id'])
                     ->will($this->onConsecutiveCalls('testCrawler', false, 'MongoId', true));

        $this->crawlerRepo->expects($this->once())->method('find')->with('MongoId')->willReturn(null);
        $this->crawlerRepo->expects($this->once())->method('findOneByName')->with('testCrawler')->willReturn(null);

        $result = $this->target->indexAction();

        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals(2, $result->getErrorLevel());
        $this->assertEquals('Crawler with name "testCrawler" does not exist.', $result->getResult());

        $result = $this->target->indexAction();

        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals(2, $result->getErrorLevel());
        $this->assertEquals('Crawler with id "MongoId" does not exist.', $result->getResult());

    }

    public function provideDeletingCrawlerTestData()
    {
        return [
            ['testCrawler', false ],
            ['testId', true ],
        ];
    }

    /**
     * @dataProvider provideDeletingCrawlerTestData
     *
     * @param $name
     * @param $id
     */
    public function testDeletingCrawler($name, $id)
    {
        $this->target->expects($this->exactly(2))->method('params')
                                                 ->withConsecutive(['name'], ['id'])
                                                 ->will($this->onConsecutiveCalls($name, $id));

        $crawler = $this->getMockBuilder(\SimpleImport\Entity\Crawler::class)->disableOriginalConstructor()
            ->setMethods(['getItems', 'getName', 'getId'])->getMock();

        /* @var \PHPUnit_Framework_MockObject_MockObject|\Zend\Console\Adapter\AdapterInterface $console */
        $console = $this->getMockBuilder(\Zend\Console\Adapter\AdapterInterface::class)
                        ->getMock();

        if ($id) {
            $this->crawlerRepo->expects($this->once())->method('find')->with($name)->willReturn($crawler);
            $this->crawlerRepo->expects($this->never())->method('findOneByName');
            $crawler->expects($this->once())->method('getName')->willReturn('fromId');
            $crawler->expects($this->once())->method('getId')->willReturn($name);
            $console->expects($this->once())->method('writeLine')->with(
                sprintf('Crawler "%s" (%s) deleted.', 'fromId', $name),
                ColorInterface::GREEN
            );
        } else {
            $this->crawlerRepo->expects($this->once())->method('findOneByName')->with($name)->willReturn($crawler);
            $this->crawlerRepo->expects($this->never())->method('find');
            $crawler->expects($this->once())->method('getName')->willReturn($name);
            $crawler->expects($this->once())->method('getId')->willReturn('fromName');
            $console->expects($this->once())->method('writeLine')->with(
                sprintf('Crawler "%s" (%s) deleted.', $name, 'fromName'),
                ColorInterface::GREEN
            );
        }

        $item = $this->getMockBuilder(\SimpleImport\Entity\Item::class)->disableOriginalConstructor()
            ->setMethods(['getDocumentId'])->getMock();
        $item->expects($this->once())->method('getDocumentId')->willReturn('jobId');

        $items = [ $item ];

        $crawler->expects($this->once())->method('getItems')->willReturn($items);

        $job = $this->getMockBuilder(\Jobs\Entity\Job::class)->disableOriginalConstructor()->setMethods(['delete'])->getMock();
        $job->expects($this->once())->method('delete');

        $this->jobRepo->expects($this->once())->method('find')->with('jobId')->willReturn($job);

        $this->crawlerRepo->expects($this->once())->method('remove')->with($crawler);

        $this->target->setConsole($console);

        $this->target->indexAction();
    }
}
