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

namespace SimpleImportTest\CrawlerProcessor;

use Cross\TestUtils\TestCase\SetupTargetTrait;
use Doctrine\Common\Collections\Collection;
use http\Exception\InvalidArgumentException;
use http\Message;
use Jobs\Entity\Job;
use Organizations\Entity\Organization;
use PHPUnit\Framework\MockObject\MockObject;
use SebastianBergmann\Environment\Runtime;
use SimpleImport\CrawlerProcessor\JobProcessor;
use SimpleImport\CrawlerProcessor\Result;
use SimpleImport\DataFetch\JsonFetch;
use SimpleImport\DataFetch\PlainTextFetch;
use SimpleImport\Entity\Crawler;
use SimpleImport\Entity\Item;
use SimpleImport\Entity\JobOptions;
use Laminas\Log\LoggerInterface;
use Jobs\Repository\Job as JobRepository;
use Laminas\Hydrator\HydrationInterface;
use Laminas\InputFilter\InputFilterInterface;
use Cross\TestUtils\TestCase\TestInheritanceTrait;
use SimpleImport\CrawlerProcessor\ProcessorInterface;
use RuntimeException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @coversDefaultClass \SimpleImport\CrawlerProcessor\JobProcessor
 */
class JobProcessorTest extends TestCase
{

    use TestInheritanceTrait, SetupTargetTrait;

    /**
     * @var JobProcessor
     */
    private $target = [
        'create' => [
            [
                'for' => 'testInheritance',
                'reflection' => JobProcessor::class
            ]
        ]
    ];

    /**
     * @var JsonFetch|MockObject
     */
    private $jsonFetch;

    /**
     * @var PlainTextFetch|MockObject
     */
    private $plainTextFetch;

    /**
     * @var JobRepository|MockObject
     */
    private $jobRepository;

    /**
     * @var HydrationInterface|MockObject
     */
    private $jobHydrator;

    /**
     * @var InputFilterInterface|MockObject
     */
    private $dataInputFilter;

    /**
     * @see TestInheritanceTrait
     *
     * @var array
     */
    private $inheritance = [ProcessorInterface::class];

    /**
     * @var string
     */
    private $lockDir;

    /**
     * @var Filesystem|MockObject
     */
    private $fs;

    /**
     * @see TestCase::setUp()
     */
    protected function initTarget()
    {
        $this->jsonFetch = $this->getMockBuilder(JsonFetch::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->plainTextFetch = $this->getMockBuilder(PlainTextFetch::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->jobRepository = $this->getMockBuilder(JobRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->jobHydrator = $this->getMockBuilder(HydrationInterface::class)
            ->getMock();

        $this->dataInputFilter = $this->getMockBuilder(InputFilterInterface::class)
            ->getMock();

        $this->fs = $this->getMockBuilder(Filesystem::class)
            ->getMock();

        $this->lockDir = __DIR__.'/../../sandbox/var/cache/simple-import';
        if(!is_dir($this->lockDir)){
            mkdir($this->lockDir,0777,true);
        }
        if(is_file($lockFile = $this->lockDir.'/crawler-id.lck')){
            unlink($lockFile);
        }

        return new JobProcessor(
            $this->jsonFetch,
            $this->plainTextFetch,
            $this->jobRepository,
            $this->jobHydrator,
            $this->dataInputFilter,
            $this->fs,
            $this->lockDir
        );
    }

    /**
     * @covers ::__construct()
     * @throws \Exception
     */
    public function testErrorWhenLockDirNotWritable()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Simple import lock dir "foo" does not exists or not writable.');
        $processor = new JobProcessor($this->jsonFetch,
            $this->plainTextFetch,
            $this->jobRepository,
            $this->jobHydrator,
            $this->dataInputFilter,
            $this->fs,
            'foo'
        );
    }

    public function testExecuteSuccessfully()
    {
        $target = $this->target;
        $jsonFetch = $this->jsonFetch;
        $crawler = $this->getMockBuilder(Crawler::class)->disableOriginalConstructor()->getMock();
        $result = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $dataInputFilter = $this->dataInputFilter;
        $jobRepository = $this->jobRepository;
        $organization = new Organization();


        $crawler->method('getId')
            ->willReturn('crawler-id');
        $crawler->method('getFeedUri')
            ->willReturn('crawlerFeedUri');

        $this->configureLockingExpectation();
        $options = new JobOptions();
        $job = new Job();
        $job->setId('job-id');
        $crawler->method('getOptions')->willReturn($options);

        $data = [
            'jobs' => [
                $job
            ]
        ];
        $item = new Item('job-id',['id' => 'job-id','link' => 'item-link']);

        $jsonFetch->expects($this->once())
            ->method('fetch')
            ->with('crawlerFeedUri')
            ->willReturn($data);

        $result->expects($this->once())
            ->method('setToProcess')
            ->with(1);
        $dataInputFilter->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $dataInputFilter->expects($this->once())
            ->method('getValues')
            ->willReturn($item->getImportData());
        $crawler->method('getItem')
            ->with($job->getId())
            ->willReturn($item);
        $crawler->method('getItems')
            ->willReturn([$item]);
        $crawler->method('getItemsToSync')
            ->willReturn([$item]);
        $crawler->method('getOrganization')
            ->willReturn($organization);

        $jobRepository->expects($this->once())
            ->method('create')
            ->with(null)
            ->willReturn($job);
        $target->execute($crawler, $result, $logger);
    }

    /**
     * @covers ::execute()
     */
    public function testExecuteErrorWhenCrawlerAlreadyRunning()
    {
        $crawler = $this->createCrawler();
        $fs = $this->fs;
        if(!is_file($lockFile = $this->lockDir.'/crawler-id.lck')){
            file_put_contents($lockFile,getmypid());
        }
        $result = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $fs->expects($this->once())
            ->method('exists')
            ->with()
            ->willReturn(true);

        $message = 'Crawler "Crawler Name (crawler-id)" already running, with pid: "'.getmypid().'"';
        $logger->expects($this->once())
            ->method('err')
            ->with($message);


        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($message);
        $target = $this->target;
        $target->execute($crawler,$result,$logger);
    }

    /**
     * @covers ::__construct()
     * @covers ::execute()
     */
    public function testExecuteWithRemoteFetchFailure()
    {
        $crawler = $this->createCrawler();

        $result = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result->expects($this->never())
            ->method('setToProcess');

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $logger->expects($this->once())
            ->method('err')
            ->with($this->stringContains('remote data failed'));

        $this->jsonFetch->expects($this->once())
            ->method('fetch')
            ->with($this->identicalTo($crawler->getFeedUri()))
            ->will($this->throwException(new RuntimeException()));

        $this->expectException(RuntimeException::class);
        $this->configureLockingExpectation();
        $this->target->execute($crawler, $result, $logger);
    }

    /**
     * @covers ::execute()
     */
    public function testExecuteWithInvalidRemoteData()
    {
        $crawler = $this->createCrawler();

        $result = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result->expects($this->never())
            ->method('setToProcess');

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $logger->expects($this->once())
            ->method('err')
            ->with($this->stringContains('Invalid data'));

        $this->jsonFetch->expects($this->once())
            ->method('fetch')
            ->with($this->identicalTo($crawler->getFeedUri()))
            ->willReturn('inv4lid data');

        $this->expectException(RuntimeException::class);
        $this->configureLockingExpectation();

        $this->target->execute($crawler, $result, $logger);
    }

    /**
     * @see https://github.com/yawik/SimpleImport/issues/24
     */
    public function testExecuteWithFailedJobRepositoryStoreMethod()
    {
        $crawler = $this->createMock(Crawler::class);
        $result = $this->createMock(Result::class);
        $logger = $this->createMock(LoggerInterface::class);
        $job = $this->createMock(Job::class);
        $item = $this->createMock(Item::class);
        $organization = $this->createMock(Organization::class);
        $jobOptions = $this->createMock(JobOptions::class);
        $itemCollection = $this->createMock(Collection::class);

        $jobRepository = $this->jobRepository;
        $jsonFetch = $this->jsonFetch;
        $dataInputFilter = $this->dataInputFilter;

        $jsonFetch->expects($this->once())
            ->method('fetch')
            ->with('some-uri')
            ->willReturn(['jobs' => [$job]]);

        $jobRepository->expects($this->once())
            ->method('store')
            ->with($job)
            ->willThrowException(new \Exception('some exception'));

        $jobRepository->expects($this->any())
            ->method('create')
            ->willReturn($job);

        // disable processing on method trackChanges
        // to remove more complicated mock
        $dataInputFilter->expects($this->any())
            ->method('getMessages')
            ->willReturn([]);

        $crawler->expects($this->once())
            ->method('getFeedUri')
            ->willReturn('some-uri');

        $crawler->expects($this->once())
            ->method('getItemsToSync')
            ->willReturn([$item]);
        $crawler->expects($this->once())
            ->method('getItems')
            ->willReturn([$item]);
        $crawler->expects($this->any())
            ->method('getOrganization')
            ->willReturn($organization);
        $crawler->expects($this->any())
            ->method('getOptions')
            ->willReturn($jobOptions);
        $crawler->expects($this->any())
            ->method('getItemsCollection')
            ->willReturn($itemCollection);

        $item->expects($this->any())
            ->method('getImportData')
            ->willReturn(['link' => 'foo']);
        $item->expects($this->once())
            ->method('getId')
            ->willReturn('item-id');
        $itemCollection->expects($this->once())
            ->method('remove')
            ->with('item-id');

        $crawler->expects($this->once())
            ->method('getId')
            ->willReturn('crawler-id');

        $this->configureLockingExpectation();

        $target = $this->target;
        $target->execute($crawler, $result, $logger);
    }

    /**
     * @return Crawler
     */
    private function createCrawler()
    {
        $crawler = new Crawler();
        $crawler
            ->setId('crawler-id')
            ->setFeedUri('crawlerFeedUri')
            ->setName('Crawler Name')
        ;
        return $crawler;
    }

    private function configureLockingExpectation()
    {
        $fs = $this->fs;
        $lockFile = $this->lockDir.'/crawler-id.lck';
        $fs->expects($this->once())
            ->method('touch')
            ->with($lockFile);
        $fs->expects($this->once())
            ->method('remove')
            ->with($lockFile);
    }
}
