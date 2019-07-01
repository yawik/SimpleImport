<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2018 Cross Solution <http://cross-solution.de>
 */

/** */
namespace SimpleImportTest\Filter;

use PHPUnit\Framework\TestCase;

use Cross\TestUtils\TestCase\SetupTargetTrait;
use Cross\TestUtils\TestCase\ContainerDoubleTrait;
use Cross\TestUtils\TestCase\TestInheritanceTrait;
use Doctrine\ODM\MongoDB\DocumentRepository;
use SimpleImport\Filter\IdToEntity;
use SimpleImport\Filter\IdToEntityFactory;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Tests for \SimpleImport\Filter\IdToEntityFactory
 *
 * @covers \SimpleImport\Filter\IdToEntityFactory
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 *
 */
class IdToEntityFactoryTest extends TestCase
{
    use TestInheritanceTrait, ContainerDoubleTrait, SetupTargetTrait;

    /**
     * @var string|IdToEntityFactory
     */
    private $target = IdToEntityFactory::class;

    private $inheritance = [ FactoryInterface::class ];

    public function testInvokationThrowsExceptionIfDocumentNameIsMissing()
    {
        $container = new ServiceManager();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Missing option "document"');

        $this->target->__invoke($container, 'irrelevant');
    }

    public function notFoundValueProvider()
    {
        return [
            [null],
            ['customNotFoundValue'],
        ];

    }

    /**
     * @dataProvider notFoundValueProvider
     *
     * @param $notFoundValue
     */
    public function testInvokationCreatesService($notFoundValue)
    {
        $documentName = 'TestEntityClass';
        $repository   = $this->getMockBuilder(DocumentRepository::class)->disableOriginalConstructor()
            ->setMethods(['find'])->getMock();
        $repository->expects($this->once())->method('find')->with('test')->willReturn(null);

        $repositories = $this->createContainerDouble(
            [
                $documentName => $repository,
            ],
            [
                'target' => AbstractPluginManager::class
            ]
        );

        $container = $this->createContainerDouble(
            [
                'repositories' => [$repositories, 1]
            ],
            [
                'target' => ServiceManager::class
            ]
        );

        $options = [
            'document' => $documentName,
            'not_found_value' => $notFoundValue
        ];

        $actual = $this->target->__invoke($container, 'irrelevant', $options);

        $this->assertInstanceOf(IdToEntity::class, $actual);
        static::assertEquals($notFoundValue ?? 'test', $actual->filter('test'));
    }
}
