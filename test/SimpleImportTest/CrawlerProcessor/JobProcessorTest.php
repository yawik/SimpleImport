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
use SimpleImport\CrawlerProcessor\JobProcessor;
use SimpleImport\CrawlerProcessor\Result;
use SimpleImport\DataFetch\JsonFetch;
use SimpleImport\DataFetch\PlainTextFetch;
use SimpleImport\Entity\Crawler;
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
     * @var JsonFetch
     */
    private $jsonFetch;

    /**
     * @var PlainTextFetch
     */
    private $plainTextFetch;

    /**
     * @var JobRepository
     */
    private $jobRepository;

    /**
     * @var HydrationInterface
     */
    private $jobHydrator;

    /**
     * @var InputFilterInterface
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
}
