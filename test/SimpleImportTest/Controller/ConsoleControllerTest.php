<?php
/**
 * YAWIK
 *
 * @filesource
 * @license    MIT
 * @copyright  2013 - 2017 Cross Solution <http://cross-solution.de>
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */

namespace SimpleImportTest\Controller;

use PHPUnit\Framework\TestCase;
use SimpleImport\Controller\ConsoleController;
use SimpleImport\Entity\Crawler;
use SimpleImport\Repository\Crawler as CrawlerRepository;
use SimpleImport\CrawlerProcessor\Manager as CrawlerProcessors;
use SimpleImport\CrawlerProcessor\ProcessorInterface;
use SimpleImport\CrawlerProcessor\Result;
use SimpleImport\Options\ModuleOptions;
use Zend\Console\ColorInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Log\LoggerInterface;
use Zend\Console\Adapter\AdapterInterface as ConsoleAdapter;
use Zend\Mvc\Console\Controller\AbstractConsoleController;
use CoreTestUtils\TestCase\TestInheritanceTrait;
use Doctrine\Common\Persistence\ObjectManager;
use DateTime;

/**
 * @coversDefaultClass \SimpleImport\Controller\ConsoleController
 * @covers \SimpleImport\Controller\ConsoleController
 */
class ConsoleControllerTest extends TestCase
{

    use TestInheritanceTrait;

    /**
     * @var ConsoleController
     */
    private $target;

    /**
     * @var CrawlerRepository
     */
    private $crawlerRepository;

    /**
     * @var CrawlerProcessors
     */
    private $crawlerProcessors;

    /**
     * @var InputFilterInterface
     */
    private $crawlerInputFilter;

    /**
     * @var ModuleOptions
     */
    private $moduleOptions;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Result
     */
    private $resultPrototype;

    /**
     * @var ConsoleAdapter
     */
    private $console;

    /**
     * @see TestInheritanceTrait
     *
     * @var array
     */
    private $inheritance = [AbstractConsoleController::class];

    /**
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->crawlerRepository = $this->getMockBuilder(CrawlerRepository::class)
            ->setMethods(['getCrawlersToImport', 'create', 'getDocumentManager', 'store', 'findOneByName', 'find'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->crawlerProcessors = $this->getMockBuilder(crawlerProcessors::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->crawlerInputFilter = $this->getMockBuilder(InputFilterInterface::class)
            ->getMock();

        $this->moduleOptions = new ModuleOptions();

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $this->resultPrototype = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->console = $this->getMockBuilder(ConsoleAdapter::class)
            ->getMock();

        $this->target = $this->getMockBuilder(ConsoleController::class)
            ->setConstructorArgs([
                $this->crawlerRepository,
                $this->crawlerProcessors,
                $this->crawlerInputFilter,
                $this->moduleOptions,
                $this->logger,
                $this->resultPrototype
            ])
            ->setMethods(['params'])
            ->getMock();

        $this->target->setConsole($this->console);
    }

    /**
     * @param int $limit
     * @param int $expected
     *
     * @covers ::__construct()
     * @covers ::importAction()
     * @dataProvider dataImportActionLimitParameter
     */
    public function testImportActionLimitParameter($limit, $expected)
    {
        $this->target->method('params')
            ->will($this->returnValueMap([
                ['limit', $limit]
            ]));

        $this->crawlerRepository->expects($this->once())
            ->method('getCrawlersToImport')
            ->with($this->identicalTo($expected))
            ->willReturn([]);

        $this->target->importAction();
    }

    /**
     * @return array
     */
    public function dataImportActionLimitParameter()
    {
        $default = 3;

        return [
            [null, $default],
            [0, $default],
            [3, 3],
            [5, 5],
            [-7, 7],
        ];
    }

    public function testImportActionNameParameter()
    {
        $this->target->method('params')->will($this->returnValueMap([
            ['limit', null],
            ['name', 'test']
        ]));

        $this->crawlerRepository->expects($this->once())
            ->method('findOneByName')
            ->with($this->identicalTo('test'))
            ->willReturn(null);

        $this->console->expects($this->once())->method('writeLine')->with($this->stringContains('no crawler with name "test"'));

        $model = $this->target->importAction();

        $this->assertEquals(1, $model->getErrorLevel());
    }

    public function testImportActionIdParameter()
    {
        $this->target->method('params')->will($this->returnValueMap([
            ['limit', null],
            ['name', 'test'],
            ['id', 'overrideName']
        ]));

        $this->crawlerRepository->expects($this->once())
                                ->method('find')
                                ->with($this->identicalTo('overrideName'))
                                ->willReturn(null);

        $this->console->expects($this->once())->method('writeLine')->with($this->stringContains('no crawler with id "overrideName"'), ColorInterface::RED);

        $model = $this->target->importAction();

        $this->assertEquals(1, $model->getErrorLevel());
    }

    public function testImportActionDoesNotProcessDelayedCrawler()
    {
        $this->target->method('params')->will($this->returnValueMap([
            ['limit', null],
            ['name', 'test']
        ]));

        $crawler =$this->getMockBuilder(\SimpleImport\Entity\Crawler::class)
            ->disableOriginalConstructor()
            ->setMethods(['canRun'])
            ->getMock();

        $crawler->expects($this->once())->method('canRun')->willReturn(false);

        $this->crawlerRepository->expects($this->once())
                                ->method('findOneByName')
                                ->with($this->identicalTo('test'))
                                ->willReturn($crawler);

        $this->console->expects($this->once())->method('writeLine')->with($this->stringContains('is still delayed'));

        $model = $this->target->importAction();

        $this->assertEquals(2,$model->getErrorLevel());


    }

    /**
     * @covers ::importAction()
     */
    public function testImportActionWithNoCrawlerToProcess()
    {
        $this->crawlerRepository->expects($this->once())
            ->method('getCrawlersToImport')
            ->willReturn([]);

         $this->console->expects($this->once())
            ->method('writeLine')
            ->with($this->stringContains('no crawler to process'))
            ->willReturn([]);

        $this->target->importAction();
    }

    public function testImportActionProcessSpecificCrawler()
    {
        $this->target->method('params')->will($this->returnValueMap([
            ['limit', null],
            ['name', 'test']
        ]));

        $crawler =$this->getMockBuilder(\SimpleImport\Entity\Crawler::class)
                       ->disableOriginalConstructor()
                       ->setMethods(['canRun'])
                       ->getMock();

        $crawler->expects($this->once())->method('canRun')->willReturn(true);

        $this->crawlerRepository->expects($this->once())
                                ->method('findOneByName')
                                ->with($this->identicalTo('test'))
                                ->willReturn($crawler);
        $crawler->setType(Crawler::TYPE_JOB);
        $now = new DateTime();

        $documentManager = $this->getMockBuilder(ObjectManager::class)
                                ->getMock();
        $documentManager->expects($this->once())
                        ->method('flush');

        $this->crawlerRepository->expects($this->once())
                                ->method('getDocumentManager')
                                ->willReturn($documentManager);

        $processor = $this->getMockBuilder(ProcessorInterface::class)
                          ->getMock();
        $processor->expects($this->once())
                  ->method('execute')
                  ->with($this->identicalTo($crawler),
                      $this->callback(function ($result) {
                          return $result instanceof Result;
                      }),
                      $this->identicalTo($this->logger)
                  );

        $this->crawlerProcessors->expects($this->once())
                                ->method('get')
                                ->with($this->identicalTo($crawler->getType()))
                                ->willReturn($processor);

        $this->target->importAction();

        $dateLastRun = $crawler->getDateLastRun();
        $this->assertInstanceOf(DateTime::class, $dateLastRun);
        $this->assertGreaterThanOrEqual($now, $dateLastRun);
    }

    /**
     * @covers ::importAction()
     */
    public function testImportActionWithCrawlersToProcess()
    {
        $crawler = new Crawler();
        $crawler->setType(Crawler::TYPE_JOB);
        $now = new DateTime();

        $documentManager = $this->getMockBuilder(ObjectManager::class)
            ->getMock();
        $documentManager->expects($this->once())
            ->method('flush');

        $this->crawlerRepository->expects($this->once())
            ->method('getCrawlersToImport')
            ->willReturn([$crawler]);
        $this->crawlerRepository->expects($this->once())
            ->method('getDocumentManager')
            ->willReturn($documentManager);

        $processor = $this->getMockBuilder(ProcessorInterface::class)
            ->getMock();
        $processor->expects($this->once())
            ->method('execute')
            ->with($this->identicalTo($crawler),
                $this->callback(function ($result) {
                    return $result instanceof Result;
                }),
                $this->identicalTo($this->logger)
            );

        $this->crawlerProcessors->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($crawler->getType()))
            ->willReturn($processor);

        $this->target->importAction();

        $dateLastRun = $crawler->getDateLastRun();
        $this->assertInstanceOf(DateTime::class, $dateLastRun);
        $this->assertGreaterThanOrEqual($now, $dateLastRun);
    }

    /**
     * @covers ::addCrawlerAction()
     */
    public function testAddCrawlerActionParameters()
    {
        $data = [
            'name' => 'crawlerName',
            'organization' => 'crawlerOrganization',
            'feedUri' => 'crawlerFeedUri',
            'runDelay' => 'crawlerRunDelay',
            'type' => 'crawlerType',
            'options' => [
                'initialState' => 'crawlerInitialState',
                'recoverState' => 'crawlerRecoverState',
            ]
        ];

        $this->target->method('params')
            ->will($this->returnValueMap([
                ['name', $data['name']],
                ['organization', $data['organization']],
                ['feed-uri', $data['feedUri']],
                ['runDelay', $this->moduleOptions->getImportRunDelay(), $data['runDelay']],
                ['type', Crawler::TYPE_JOB, $data['type']],
                ['jobInitialState', $data['options']['initialState']],
                ['jobRecoverState', $data['options']['recoverState']],
            ]));

        $this->crawlerInputFilter->expects($this->once())
            ->method('getMessages')
            ->willReturn([]);

        $this->crawlerInputFilter->expects($this->once())
            ->method('setData')
            ->with($this->equalTo($data));

        $this->target->addCrawlerAction();
    }

    /**
     * @covers ::addCrawlerAction()
     */
    public function testAddCrawlerActionWithInvalidData()
    {
        $this->console->expects($this->exactly(2))
            ->method('writeLine')
            ->withConsecutive(
                [$this->stringContains('Invalid parameters')],
                [$this->stringContains('key: firstMessage, secondMessage')]
            );

        $this->crawlerInputFilter->expects($this->once())
            ->method('getMessages')
            ->willReturn([
                'key' => ['firstMessage', 'secondMessage']
            ]);

        $this->target->addCrawlerAction();
    }

    /**
     * @covers ::addCrawlerAction()
     */
    public function testAddCrawlerActionWithValidData()
    {
        $values = [
            'name' => 'crawlerName',
            'organization' => 'crawlerOrganization',
            'feedUri' => 'crawlerFeedUri',
            'runDelay' => 'crawlerRunDelay',
            'type' => 'crawlerType',
            'options' => [
                'initialState' => 'crawlerInitialState'
            ]
        ];
        $crawler = new Crawler();
        $crawler->setType(Crawler::TYPE_JOB);

        $this->crawlerInputFilter->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $this->crawlerInputFilter->expects($this->once())
            ->method('getValues')
            ->willReturn($values);

        $this->crawlerRepository->expects($this->once())
            ->method('create')
            ->with($this->equalTo($values))
            ->willReturn($crawler);
        $this->crawlerRepository->expects($this->once())
            ->method('store')
            ->with($this->identicalTo($crawler));

        $this->console->expects($this->once())
            ->method('writeLine')
            ->with($this->stringContains('successfully added'));

        $this->target->addCrawlerAction();
    }
}
