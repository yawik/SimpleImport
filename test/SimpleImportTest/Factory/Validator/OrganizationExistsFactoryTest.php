<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */

namespace SimpleImportTest\Factory\Validator;

use Interop\Container\ContainerInterface;
use SimpleImport\Factory\Validator\OrganizationExistsFactory;
use SimpleImport\Validator\OrganizationExists;
use Organizations\Repository\Organization as OrganizationRepository;

/**
 * @coversDefaultClass \SimpleImport\Factory\Validator\OrganizationExistsFactory
 */
class OrganizationExistsFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers ::__invoke
     */
    public function testInvoke()
    {
        $organizationsRepository = $this->getMockBuilder(OrganizationRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repositories = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $repositories->expects($this->once())
            ->method('get')
            ->with($this->equalTo('Organizations'))
            ->willReturn($organizationsRepository);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $container->expects($this->exactly(1))
            ->method('get')
            ->will($this->returnValueMap([
                ['repositories', $repositories],
            ]));


        $organizationExists = (new OrganizationExistsFactory())->__invoke($container, OrganizationExists::class);
        $this->assertInstanceOf(OrganizationExists::class, $organizationExists);
    }
}
