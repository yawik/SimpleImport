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
use Jobs\Entity\Job;
use Organizations\Entity\Organization;
use PHPUnit\Framework\MockObject\MockObject;
use SimpleImport\CrawlerProcessor\JobProcessor;
use SimpleImport\CrawlerProcessor\Result;
use SimpleImport\DataFetch\JsonFetch;
use SimpleImport\DataFetch\PlainTextFetch;
use SimpleImport\Entity\Crawler;
use SimpleImport\Entity\Item;
use SimpleImport\Entity\JobOptions;
use Zend\Log\LoggerInterface;
use Jobs\Repository\Job as JobRepository;
use Zend\Hydrator\HydrationInterface;
use Zend\InputFilter\InputFilterInterface;
use Cross\TestUtils\TestCase\TestInheritanceTrait;
use SimpleImport\CrawlerProcessor\ProcessorInterface;
use RuntimeException;
use PHPUnit\Framework\TestCase;

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

        return new JobProcessor(
            $this->jsonFetch,
            $this->plainTextFetch,
            $this->jobRepository,
            $this->jobHydrator,
            $this->dataInputFilter
        );
    }

    /**
     * @covers ::__construct()
     * @covers ::execute()
     */
    public function testExecuteWithRemoteFetchFailure()
    {
        $crawler = new Crawler();
        $crawler->setFeedUri('crawlerFeedUri');

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

        $this->target->execute($crawler, $result, $logger);
    }

    /**
     * @covers ::execute()
     */
    public function testExecuteWithInvalidRemoteData()
    {
        $crawler = new Crawler();
        $crawler->setFeedUri('crawlerFeedUri');

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

        $target = $this->target;
        $target->execute($crawler, $result, $logger);
    }

}
