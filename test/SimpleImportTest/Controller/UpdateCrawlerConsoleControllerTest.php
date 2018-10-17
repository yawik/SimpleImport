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
use CoreTestUtils\TestCase\TestSetterGetterTrait;
use Organizations\Entity\Organization;
use Organizations\Entity\OrganizationName;
use SimpleImport\Controller\Plugin\LoadCrawler;
use SimpleImport\Controller\UpdateCrawlerConsoleController;
use SimpleImport\Entity\Crawler;
use SimpleImport\InputFilter\CrawlerInputFilter;
use Zend\Mvc\Console\Controller\AbstractConsoleController;

/**
 * Tests for \SimpleImport\Controller\UpdateCrawlerConsoleController
 * 
 * @covers \SimpleImport\Controller\UpdateCrawlerConsoleController
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 *  
 */
class UpdateCrawlerConsoleControllerTest extends \PHPUnit_Framework_TestCase
{
    use TestInheritanceTrait, TestSetterGetterTrait;

    /**
     *
     *
     * @var array|\PHPUnit_Framework_MockObject_MockObject|UpdateCrawlerConsoleController|\ReflectionClass
     */
    private $target = [
        UpdateCrawlerConsoleController::class,
        'mock' => [ 'plugin', 'params' ],
        '@testInheritance' => [ 'as_reflection' => true ],
        '@testSetterAndGetter' => [
            'mock' => null,
        ],
    ];

    private $inheritance = [ AbstractConsoleController::class ];

    public function propertiesProvider() {
        $filter = new CrawlerInputFilter();

        return [
            ['inputFilter', ['value' => $filter, 'expect_property' => $filter]]
        ];
    }

    public function testIndexActionPrintsSingleCrawlerInfo()
    {
        $date    = new \DateTime();
        $org     = new Organization();
        $crawler = new Crawler();

        $org->setOrganizationName(new OrganizationName('TestCompany'));
        $org->setId('TestOrgId');

        $crawler->setId('TestId');
        $crawler->setType(Crawler::TYPE_JOB);
        $crawler->setName('TestCrawler');
        $crawler->setFeedUri('TestUri');
        $crawler->setRunDelay(1234);
        $crawler->getOptions()->setInitialState('active');
        $crawler->setDateLastRun($date);
        $crawler->setOrganization($org);

        $loader = $this->getMockBuilder(LoadCrawler::class)->disableOriginalConstructor()->setMethods(['__invoke'])->getMock();
        $loader->expects($this->once())->method('__invoke')->willReturn($crawler);

        $this->target->expects($this->once())->method('params')->with('name')->willReturn('TestCrawler');
        $this->target->expects($this->once())->method('plugin')->with('siLoadCrawler')->willReturn($loader);


        $output = $this->target->indexAction();

        $this->assertContains('TestCrawler (TestId) [' . Crawler::TYPE_JOB . ']', $output);
        $this->assertContains('Organization: TestCompany (TestOrgId)', $output);
        $this->assertContains('TestUri', $output);
        $this->assertContains('1234', $output);
        $this->assertContains('Jobs initial state: active', $output);
        $this->assertContains($date->format('d.m.Y H:i:s'), $output);
    }

    public function testIndexActionPrintsListOfCrawlers()
    {
        $this->target->expects($this->once())->method('params')->with('name')->willReturn(null);

        $crawler1 = new Crawler();
        $crawler1->setName('Crawler1');
        $crawler1->setId('Id1');

        $crawler2 = new Crawler();
        $crawler2->setName('Crawler2');
        $crawler2->setId('Id2');

        $set = [ $crawler1, $crawler2 ];

        $loader = $this->getMockBuilder(LoadCrawler::class)->disableOriginalConstructor()->setMethods(['loadAll'])->getMock();
        $loader->expects($this->once())->method('loadAll')->willReturn($set);

        $this->target->expects($this->once())->method('plugin')->with('siLoadCrawler')->willReturn($loader);

        $this->expectOutputRegex('~Crawler1\.* \(Id1\)' . PHP_EOL . 'Crawler2\.* \(Id2\)~');

        $this->target->indexAction();

    }

    private function setupUpdateActionTest($inputFilterValid)
    {
        $return = [];
        $params = $this->getMockBuilder(\Zend\Mvc\Controller\Plugin\Params::class)
            ->disableOriginalConstructor()->setMethods(['__invoke'])->getMock();

        $params->expects($this->exactly(6))->method('__invoke')
            ->with($this->callback(function($arg) {
                return in_array($arg, ['rename', 'feed-uri', 'organization', 'rundelay', 'type', 'jobInitialState']);
            }))
            ->willReturn(null);

        $crawler = new Crawler();
        $crawler->setType(Crawler::TYPE_JOB);

        $return['crawler'] = $crawler;


        $loader = $this->getMockBuilder(LoadCrawler::class)->disableOriginalConstructor()
            ->setMethods(['__invoke', 'store'])->getMock();

        $return['loader'] = $loader;

        $loader->expects($this->once())->method('__invoke')->willReturn($crawler);

        $inputFilter = $this->getMockBuilder(CrawlerInputFilter::class)
                            ->disableOriginalConstructor()
            ->setMethods(['setData', 'isValid', 'getMessages', 'getValues'])
            ->getMock();

        $inputFilter->expects($this->once())->method('setData')->with([
            'name' => null,
            'feedUri' => null,
            'organization' => null,
            'runDelay' => null,
            'type' => null,
            'options' => ['initialState' => null]
        ]);

        $inputFilter->expects($this->once())->method('isValid')->willReturn($inputFilterValid
        );
        if ($inputFilterValid) {
            $org = $return['org'] = new Organization();
            $inputFilter->expects($this->never())->method('getMessages');
            $inputFilter->expects($this->once())->method('getValues')->willReturn(
                [
                    'name' => 'UpdateName',
                    'feedUri' => 'UpdateFeedUri',
                    'organization' => $org,
                    'runDelay' =>  '4321',
                    'type' => Crawler::TYPE_JOB,
                    'options' => [ 'initialState' => 'active' ],
                ]
            );
        } else {

            $inputFilter->expects($this->once())->method('getMessages')->willReturn(
                [
                    'field1' => ['empty' => 'Test error message 1', 'Message1.1'],
                    'field2' => ['error' => 'Error Message 2']

                ]
            );
            $inputFilter->expects($this->never())->method('getValues');
        }

        $this->target->expects($this->exactly(2))->method('plugin')->will($this->returnValueMap(
            [
                ['params', null, $params],
                ['siLoadCrawler', null, $loader],
            ]
        ));

        $this->target->setInputFilter($inputFilter);

        return $return;
    }

    public function testUpdateActionInvalidParametersThrowsException()
    {
        $this->setupUpdateActionTest(false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageRegExp('~^Invalid parameter.*field1.*Test error message 1, Message1\.1.*field2.*Error Message 2~s');

        $this->target->updateAction();

    }

    public function testUpdateAction()
    {
        /* @var Organization $org
         * @var Crawler $crawler
         */
        $mocks = $this->setupUpdateActionTest(true);
        extract($mocks);

        $org->setOrganizationName(new OrganizationName('CompanyName'));
        $crawler->setDateLastRun(new \DateTime());

        $console = $this->getMockBuilder(\Zend\Console\Adapter\AdapterInterface::class)
            ->getMock();

        $console->expects($this->once())->method('writeLine')->with($this->stringContains('Crawler updated'));

        $this->target->setConsole($console);

        $this->target->updateAction();

        $this->assertSame($org, $crawler->getOrganization());
        $this->assertEquals('UpdateName', $crawler->getName());
    }
}
