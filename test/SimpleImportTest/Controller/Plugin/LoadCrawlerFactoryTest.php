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
use Cross\TestUtils\TestCase\ContainerDoubleTrait;
use Cross\TestUtils\TestCase\TestInheritanceTrait;

use SimpleImport\Controller\Plugin\LoadCrawler;
use SimpleImport\Controller\Plugin\LoadCrawlerFactory;
use SimpleImport\Repository\Crawler;
use Zend\ServiceManager\Factory\FactoryInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests for \SimpleImport\Controller\Plugin\LoadCrawlerFactory
 *
 * @covers \SimpleImport\Controller\Plugin\LoadCrawlerFactory
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 *
 */
class LoadCrawlerFactoryTest extends TestCase
{
    use TestInheritanceTrait, ContainerDoubleTrait, SetupTargetTrait;

    private $target = LoadCrawlerFactory::class;

    private $inheritance = [ FactoryInterface::class ];

    public function p() { return ['set' => ['test']];}
    /**
     * @dataProvider p
     * @return void
     */
    public function testCreatesService($p)
    {
        $repository = $this->getMockBuilder(Crawler::class)
                           ->disableOriginalConstructor()->getMock();

        $repositories = $this->createContainerDouble(
            [
                'SimpleImport/Crawler' => [$repository, 1]
            ]
        );

        $container = $this->createContainerDouble(
            ['repositories' => [$repositories, 1]],
            ['target' => \Interop\Container\ContainerInterface::class]
        );

        $service = $this->target->__invoke($container, 'irrelevant');

        $this->assertInstanceOf(LoadCrawler::class, $service);
    }
}
