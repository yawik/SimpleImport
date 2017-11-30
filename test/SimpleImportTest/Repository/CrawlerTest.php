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

namespace SimpleImportTest\Repository;

use SimpleImport\Repository\Crawler as CrawlerRepository;
use SimpleImport\Entity\Crawler;
use Organizations\Entity\Organization;
use Core\Repository\AbstractRepository;
use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Doctrine\ODM\MongoDB\Query\Query;
use CoreTestUtils\TestCase\TestInheritanceTrait;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\UnitOfWork;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use DateTime;
use stdClass;

/**
 * @coversDefaultClass \SimpleImport\Repository\Crawler
 */
class CrawlerTest extends \PHPUnit_Framework_TestCase
{

    use TestInheritanceTrait;

    /**
     * @var CrawlerRepository
     */
    private $target;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var Query
     */
    private $query;

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @see TestInheritanceTrait
     *
     * @var array
     */
    private $inheritance = [AbstractRepository::class];

    /**
     * @see \PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryBuilder->method('getQuery')
            ->willReturn($this->query);

        $this->dm = $this->getMockBuilder(DocumentManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dm->method('getEventManager')
            ->willReturn(new \Doctrine\Common\EventManager());

        $uow = $this->getMockBuilder(UnitOfWork::class)
            ->disableOriginalConstructor()
            ->getMock();

        $classMetadata = $this->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->target = $this->getMockBuilder(CrawlerRepository::class)
            ->setConstructorArgs([$this->dm, $uow, $classMetadata])
            ->setMethods(['createQueryBuilder'])
            ->getMock();
        $this->target->method('createQueryBuilder')
            ->willReturn($this->queryBuilder);
        $this->target->setEntityPrototype(new Crawler());
    }

    /**
     * @covers ::create()
     */
    public function testCreateDefault()
    {
        /** @var Crawler $crawler */
        $crawler = $this->target->create([]);
        $this->assertInstanceOf(Crawler::class, $crawler);
        $this->assertNull($crawler->getOrganization());

        $dateLastRun = $crawler->getDateLastRun();
        $this->assertInstanceOf(DateTime::class, $dateLastRun);
        $this->assertEquals(0, $dateLastRun->getTimestamp());
    }

    /**
     * @covers ::create()
     */
    public function testCreateWithOrganization()
    {
        $organizationId = 'organizationId';
        $organization = new Organization();

        $this->dm->expects($this->once())
            ->method('getReference')
            ->with($this->identicalTo(Organization::class), $this->identicalTo($organizationId))
            ->willReturn($organization);

        /** @var Crawler $crawler */
        $crawler = $this->target->create(['organization' => $organizationId]);
        $this->assertInstanceOf(Crawler::class, $crawler);
        $this->assertSame($organization, $crawler->getOrganization());
    }

    /**
     * @covers ::getCrawlersToImport()
     * @dataProvider dataGetCrawlersToImport
     */
    public function testGetCrawlersToImport($limit)
    {
        $result = new stdClass();

        $this->queryBuilder->expects($this->once())
            ->method('where')
            ->with($this->stringContains('this.dateLastRun.date < new Date(ISODate().getTime() - 1000 * 60 * this.runDelay);'))
            ->willReturnSelf();
        $this->queryBuilder->expects($this->once())
            ->method('sort')
            ->with(['dateLastRun.date' => 1])
            ->willReturnSelf();
        $this->queryBuilder->expects($this->exactly(isset($limit) ? 1 : 0))
            ->method('limit')
            ->with($limit)
            ->willReturnSelf();

        $this->query->expects($this->once())
            ->method('execute')
            ->willReturn($result);

        $this->assertSame($result, $this->target->getCrawlersToImport($limit));
    }

    /**
     * @return array
     */
    public function dataGetCrawlersToImport()
    {
        return [
            [null],
            [10]
        ];
    }
}
