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

use Organizations\Entity\Organization;
use Organizations\Entity\OrganizationName;
use PHPUnit\Framework\TestCase;
use SimpleImport\Controller\Plugin\LoadCrawler;
use SimpleImport\Controller\UpdateCrawlerConsoleController;
use SimpleImport\Entity\Crawler;
use SimpleImport\InputFilter\CrawlerInputFilter;
use Zend\Mvc\Console\Controller\AbstractConsoleController;

use Zend\Mvc\Controller\Plugin\Params;

use Zend\Mvc\Controller\PluginManager;

use Zend\Stdlib\Parameters;
use PHPUnit\Framework\TestCase;

/**
 * Tests for \SimpleImport\Controller\UpdateCrawlerConsoleController
 *
 * @covers \SimpleImport\Controller\UpdateCrawlerConsoleController
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 *
 */
class UpdateCrawlerConsoleControllerTest extends TestCase
{
    use SetupTargetTrait, TestInheritanceTrait, ContainerDoubleTrait;

    /**
     *
     *
     * @var array|\PHPUnit_Framework_MockObject_MockObject|UpdateCrawlerConsoleController|\ReflectionClass
     */
    private $target = [
        'create' => [
            [
                'for' => 'testInheritance',
                'reflection' => UpdateCrawlerConsoleController::class,
            ],
            [
                'for' => 'testSetterAndGetter',
                'target' => UpdateCrawlerConsoleController::class,
            ],
        ],
        // UpdateCrawlerConsoleController::class,
        // 'mock' => [ 'plugin', 'params' ],
        // '@testInheritance' => [ 'as_reflection' => true ],
        // '@testSetterAndGetter' => [
        //     'mock' => null,
        // ],
    ];

    private $inheritance = [ AbstractConsoleController::class ];

    private function initTarget()
    {
        $this->params = new class extends Params
        {
            public function __construct()
            {
                $this->params = new Parameters();
            }

            public function set($name, $value)
            {
                $this->params->set($name, $value);
            }

            public function __invoke($param=null,  $default=null)
            {
                return $this->params->get($param, $default);
            }
        };

        $loader = new class extends LoadCrawler
        {
            public $crawler;
            public $set;
            public $storeCalledWith;

            public function __construct()
            {

            }

            public function __invoke()
            {
                return $this->crawler;
            }
            public function loadAll()
            {
                return $this->set;
            }
            public function store($crawler)
            {
                $this->storeCalledWith = $crawler;
            }
        };

        $plugins = $this->createContainerProphecy(
            [
                'params' => $this->params,
                'siLoadCrawler' => $loader,
            ],
            ['target' => PluginManager::class, 'args_get' => [null]]
        );

        $target = new UpdateCrawlerConsoleController();
        $plugins->setController($target)->shouldBeCalled();
        $target->setPluginManager($plugins->reveal());
        $this->loader = $loader;
        return $target;
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

        $this->loader->crawler = $crawler;

        $this->params->set('name', 'TestCrawler');

        $output = $this->target->indexAction();

        $this->assertStringContainsString('TestCrawler (TestId) [' . Crawler::TYPE_JOB . ']', $output);
        $this->assertStringContainsString('Organization: TestCompany (TestOrgId)', $output);
        $this->assertStringContainsString('TestUri', $output);
        $this->assertStringContainsString('1234', $output);
        $this->assertStringContainsString('Jobs initial state: active', $output);
        $this->assertStringContainsString($date->format('d.m.Y H:i:s'), $output);
    }

    public function testIndexActionPrintsListOfCrawlers()
    {
        //$this->params->set('name', null);

        $crawler1 = new Crawler();
        $crawler1->setName('Crawler1');
        $crawler1->setId('Id1');

        $crawler2 = new Crawler();
        $crawler2->setName('Crawler2');
        $crawler2->setId('Id2');

        $this->loader->set = [ $crawler1, $crawler2 ];

        $this->expectOutputRegex('~Crawler1\.* \(Id1\)' . PHP_EOL . 'Crawler2\.* \(Id2\)~');

        $this->target->indexAction();

    }

    private function setupUpdateActionTest($inputFilterValid)
    {
        $return = [];
        $params = $this->getMockBuilder(\Zend\Mvc\Controller\Plugin\Params::class)
            ->disableOriginalConstructor()->setMethods(['__invoke'])->getMock();

        foreach (['rename', 'feed-uri', 'organization', 'rundelay', 'type', 'jobInitialState', 'jobRecoverState'] as $key) {
            $this->params->set($key, null);
        }

        $crawler = new Crawler();
        $crawler->setType(Crawler::TYPE_JOB);

        $return['crawler'] = $crawler;


        $this->loader->crawler = $crawler;

        $return['loader'] = $this->loader;

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
            'options' => ['initialState' => null, 'recoverState' => null]
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
                    'options' => [ 'initialState' => 'active', 'recoverState' => 'active' ],
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

        $this->target->setInputFilter($inputFilter);

        return $return;
    }

    public function testUpdateActionInvalidParametersThrowsException()
    {
        $this->setupUpdateActionTest(false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageRegExp('~^Invalid parameter.*field1.*Test error message 1.*Message1\.1.*field2.*Error Message 2~s');

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
        $this->assertSame($crawler, $this->loader->storeCalledWith);
    }
}
