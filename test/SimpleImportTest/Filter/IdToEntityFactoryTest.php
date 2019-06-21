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

use CoreTestUtils\TestCase\ServiceManagerMockTrait;
use CoreTestUtils\TestCase\TestInheritanceTrait;
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
    use TestInheritanceTrait, ServiceManagerMockTrait;

    /**
     * @var string|IdToEntityFactory
     */
    private $target = IdToEntityFactory::class;

    private $inheritance = [ FactoryInterface::class ];

    public function testInvokationThrowsExceptionIfDocumentNameIsMissing()
    {
        $container = $this->getServiceManagerMock();

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
        $repository   = $this->getMockBuilder(DocumentRepository::class)->disableOriginalConstructor()->getMock();

        $container = $this->getServiceManagerMock();

        $repositories = $this->getPluginManagerMock([
            $documentName => $repository,
        ], $container);

        $container->setService('repositories', $repositories);
        $container->setExpectedCallCount('get', 'repositories', 1);

        $options = [
            'document' => $documentName,
            'not_found_value' => $notFoundValue
        ];

        $actual = $this->target->__invoke($container, 'irrelevant', $options);

        $this->assertInstanceOf(IdToEntity::class, $actual);
        $this->assertAttributeEquals($notFoundValue, 'notFoundValue', $actual);
    }
}
