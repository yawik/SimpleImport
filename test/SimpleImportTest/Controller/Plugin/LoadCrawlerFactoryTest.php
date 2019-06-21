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

use CoreTestUtils\TestCase\ServiceManagerMockTrait;
use CoreTestUtils\TestCase\TestInheritanceTrait;
use PHPUnit\Framework\TestCase;
use SimpleImport\Controller\Plugin\LoadCrawler;
use SimpleImport\Controller\Plugin\LoadCrawlerFactory;
use SimpleImport\Repository\Crawler;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Tests for \SimpleImport\Controller\Plugin\LoadCrawlerFactory
 * 
 * @covers \SimpleImport\Controller\Plugin\LoadCrawlerFactory
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 *  
 */
class LoadCrawlerFactoryTest extends TestCase
{
    use TestInheritanceTrait, ServiceManagerMockTrait;

    private $target = LoadCrawlerFactory::class;

    private $inheritance = [ FactoryInterface::class ];

    public function testCreatesService()
    {
        $container = $this->getServiceManagerMock();
        $repository = $this->getMockBuilder(Crawler::class)
                           ->disableOriginalConstructor()->getMock();

        $repositories = $this->createPluginManagerMock(
            [
                'SimpleImport/Crawler' => ['service' => $repository, 'count_get' => 1]
            ],
            $container
        );

        $container->setService('repositories', $repositories);
        $container->setExpectedCallCount('get', 'repositories', 1);

        $service = $this->target->__invoke($container, 'irrelevant');

        $this->assertInstanceOf(LoadCrawler::class, $service);
        $this->assertAttributeSame($repository, 'repository', $service);
    }
}
