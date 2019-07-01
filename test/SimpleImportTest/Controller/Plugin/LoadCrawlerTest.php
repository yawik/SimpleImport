<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2018 Cross Solution <http://cross-solution.de>
 */

/** */
namespace SimpleImportTest\Controller\Plugin;


use Cross\TestUtils\TestCase\SetupTargetTrait;
use Cross\TestUtils\TestCase\TestInheritanceTrait;

use SimpleImport\Controller\Plugin\LoadCrawler;
use SimpleImport\Entity\Crawler;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use SimpleImport\Repository\Crawler as CrawlerRepository;
use Zend\Mvc\Controller\Plugin\Params;
use PHPUnit\Framework\TestCase;

/**
 * Tests for \SimpleImport\Controller\Plugin\LoadCrawler
 *
 * @covers \SimpleImport\Controller\Plugin\LoadCrawler
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 */
class LoadCrawlerTest extends TestCase
{
    //use TestInheritanceTrait;
    use SetupTargetTrait, TestInheritanceTrait;

    private $target = [
        'create' => [
            [
                'for' => ['testInheritance'],
                'reflection' => LoadCrawler::class,
            ],
        ],
    ];

    private $inheritance = [ AbstractPlugin::class ];

    /**
     *
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|CrawlerRepository
     */
    private $crawlerRepositoryMock;

    private function initTarget()
    {
        $repository = $this->getMockBuilder(CrawlerRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['find', 'findOneByName', 'findAll', 'store'])
            ->getMock();

        $this->crawlerRepositoryMock = $repository;

        return [LoadCrawler::class, $repository];
    }

    private function setupControllerMock($name, $id)
    {
        $params = $this->getMockBuilder(Params::class)->disableOriginalConstructor()->getMock();
        $params->expects(self::exactly(2))->method('__invoke')->will($this->returnValueMap([
            ['name', null, $name],
            ['id', null, $id ?: null],
        ]));

        /* @var AbstractActionController|\PHPUnit_Framework_MockObject_MockObject $controller */
        $controller = $this->getMockBuilder(AbstractActionController::class)
            ->disableOriginalConstructor()
            ->setMethods(['plugin'])
            ->getMock();

        $controller->expects($this->once())->method('plugin')->with('params')->willReturn($params);

        $this->target->setController($controller);
    }

    public function provideTestData()
    {
        return [
            ['useName', false],
            ['useId', true],
        ];
    }

    /**
     * @dataProvider provideTestData
     *
     * @param $name
     * @param $byId
     */
    public function testThrowsExceptionIfNoCrawlerIsFound($name, $byId)
    {
        $this->setupControllerMock($name, $byId);

        $expectMethod = $byId ? 'find' : 'findOneByName';
        $notExpectMethod = $byId ? 'findOneByName' : 'find';
        $this->crawlerRepositoryMock->expects($this->once())->method($expectMethod)->willReturn(null);
        $this->crawlerRepositoryMock->expects($this->never())->method($notExpectMethod);

        $expectedMessage = 'Crawler with ' . ($byId ? 'id' : 'name');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->target->__invoke();
    }

    /**
     * @dataProvider provideTestData
     *
     * @param $name
     * @param $byId
     */
    public function testLoadsCrawler($name, $byId)
    {
        $this->setupControllerMock($name, $byId);

        $expected = new Crawler();

        $repoMethod = $byId ? 'find' : 'findOneByName';
        $this->crawlerRepositoryMock->expects($this->once())->method($repoMethod)->willReturn($expected);

        $crawler = $this->target->__invoke();

        $this->assertSame($expected, $crawler);
    }

    public function testLoadsAllCrawler()
    {
        $this->crawlerRepositoryMock->expects($this->once())->method('findAll');

        $this->target->loadAll();
    }

    public function testStoresACrawler()
    {
        $crawler = new Crawler();

        $this->crawlerRepositoryMock->expects($this->once())->method('store')->with($crawler);

        $this->target->store($crawler);
    }
}
