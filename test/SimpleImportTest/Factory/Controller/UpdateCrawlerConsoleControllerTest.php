<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2018 Cross Solution <http://cross-solution.de>
 */
  
/** */
namespace SimpleImportTest\Factory\Controller;

use CoreTestUtils\TestCase\ServiceManagerMockTrait;
use CoreTestUtils\TestCase\TestInheritanceTrait;
use PHPUnit\Framework\TestCase;
use SimpleImport\Controller\UpdateCrawlerConsoleController;
use SimpleImport\Factory\Controller\UpdateCrawlerConsoleControllerFactory;
use SimpleImport\InputFilter\CrawlerInputFilter;
use Zend\Mvc\Console\Router\RouteMatch;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Tests for \SimpleImport\Factory\Controller\UpdateCrawlerConsoleControllerFactory
 * 
 * @covers \SimpleImport\Factory\Controller\UpdateCrawlerConsoleControllerFactory
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 *  
 */
class UpdateCrawlerConsoleControllerTest extends TestCase
{
    use TestInheritanceTrait, ServiceManagerMockTrait;

    private $target = UpdateCrawlerConsoleControllerFactory::class;

    private $inheritance = [ FactoryInterface::class ];

    private function setupContainer($isIndexRoute)
    {
        $application = $this->getMockBuilder(\Zend\Mvc\Application::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMvcEvent'])
            ->getMock();

        $event = new MvcEvent();
        $routeMatch = new RouteMatch([]);
        $routeMatch->setParam('action', $isIndexRoute ? 'index' : 'update');
        $event->setRouteMatch($routeMatch);

        $application->expects($this->once())->method('getMvcEvent')->willReturn($event);

        if ($isIndexRoute) {
            $container = $this->getServiceManagerMock([
                'Application' => ['service' => $application, 'count_get' => 1],
                'InputFilterManager' => ['service' => null, 'count_get' => 0],
            ]);

            return $container;

        }

        $container = $this->getServiceManagerMock([
            'Application' => ['service' => $application, 'count_get' => 1],
        ]);

        $filter = new CrawlerInputFilter();
        $filters = $this->createPluginManagerMock([
             CrawlerInputFilter::class => ['service' => $filter, 'count_get' => 1]
        ], $container);

        $container->setService('InputFilterManager', $filters);
        $container->setExpectedCallCount('get', 'InputFilterManager', 1);

        return [$container, $filter];

    }

    public function testCreatesServiceWithoutInjectingInputFilter()
    {
        $container = $this->setupContainer(true);

        $controller = $this->target->__invoke($container, 'irrelevant');

        $this->assertInstanceOf(UpdateCrawlerConsoleController::class, $controller);
        $this->assertAttributeEmpty('inputFilter', $controller);
    }

    public function testCreatesServiceWithInputFilterInjection()
    {
        list($container, $filter) = $this->setupContainer(false);

        $controller = $this->target->__invoke($container, 'irrelevant');

        $this->assertAttributeSame($filter, 'inputFilter', $controller);
    }

}
