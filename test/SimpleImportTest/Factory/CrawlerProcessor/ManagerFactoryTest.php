<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */

namespace SimpleImportTest\Factory\CrawlerProcessor;

use Interop\Container\ContainerInterface;
use SimpleImport\Factory\CrawlerProcessor\ManagerFactory;
use SimpleImport\CrawlerProcessor\Manager;

/**
 * @coversDefaultClass \SimpleImport\Factory\CrawlerProcessor\ManagerFactory
 */
class ManagerFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers ::__invoke
     */
    public function testInvoke()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $container->expects($this->exactly(1))
            ->method('get')
            ->will($this->returnValueMap([
                ['Config', ['simple_import_crawler_processor_manager' => []]],
            ]));


        $manager = (new ManagerFactory())->__invoke($container, Manager::class);
        $this->assertInstanceOf(Manager::class, $manager);
    }
}
