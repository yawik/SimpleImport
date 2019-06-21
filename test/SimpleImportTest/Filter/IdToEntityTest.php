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

use CoreTestUtils\TestCase\TestInheritanceTrait;
use CoreTestUtils\TestCase\TestSetterGetterTrait;
use Doctrine\ODM\MongoDB\DocumentRepository;
use SimpleImport\Filter\IdToEntity;
use Zend\Filter\FilterInterface;

/**
 * Tests for \SimpleImport\Filter\IdToEntity
 * 
 * @covers \SimpleImport\Filter\IdToEntity
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 *  
 */
class IdToEntityTest extends TestCase
{
    use TestInheritanceTrait, TestSetterGetterTrait;

    /**
     *
     *
     * @var array|\PHPUnit_Framework_MockObject_MockObject|IdToEntity|\ReflectionClass
     */
    private $target = [
        IdToEntity::class,
        'setupMocks',
        '@testInheritance' => ['as_reflection' => true],
        '@testSetterAndGetter' => [ 'mock' => ['__invoke'], 'args' => false ],
        '@testInvokation' => [ 'mock' => ['filter'], 'args' => false ]
    ];

    private $inheritance = [ FilterInterface::class ];

    private $properties = [
        [ 'notFoundValue', ['value' => 'customNotFoundValue', 'expect_property' => 'customNotFoundValue' ]],
    ];

    /**
     *
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|DocumentRepository
     */
    private $repositoryMock;

    private function setupMocks()
    {
        $this->repositoryMock = $this->getMockBuilder(DocumentRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['find', 'getDocumentName'])
            ->getMock();

        return [$this->repositoryMock];
    }
    /**
     * @testdox Invokation proxies to filter
     */
    public function testInvokation()
    {
        $value = 'testId';

        $this->target->expects($this->once())->method('filter')->with($value);

        $this->target->__invoke($value);
    }

    public function nullValuesProvider()
    {
        return [
            [null],
            [[]],
            [false],
            [''],
            [0],
        ];
    }

    /**
     * @dataProvider nullValuesProvider
     *
     * @param $value
     */
    public function testFilterReturnsNullOnEmptyValues($value)
    {
        $this->repositoryMock->expects($this->never())->method('find');
        $this->repositoryMock->expects($this->never())->method('getDocumentName');

        $this->assertNull($this->target->filter($value));
    }

    public function idValuesProvider()
    {
        return [
            ['testId'],
            [new \MongoId()]
        ];
    }

    /**
     * @dataProvider idValuesProvider
     *
     * @param $value
     */
    public function testFilterReturnsEntity($value)
    {
        $entity = new \stdClass;

        $this->repositoryMock->expects($this->once())->method('find')->with($value)->willReturn($entity);
        $this->repositoryMock->expects($this->never())->method('getDocumentName');

        $this->assertSame($entity, $this->target->filter($value));
    }

    /**
     * @dataProvider idValuesProvider
     *
     * @param $value
     */
    public function testFilterReturnsNotFoundValue($value)
    {
        if (!is_string($value)) {
            $notFoundValue = 'testNotFoundValue';
            $this->target->setNotFoundValue($notFoundValue);
        } else {
            $notFoundValue = $value;
        }

        $this->repositoryMock->expects($this->once())->method('find')->with($value)->willReturn(null);

        $this->assertEquals($notFoundValue, $this->target->filter($value));
    }

    public function testFilterReturnsValueIfItsAnEntity()
    {
        $entity = new \stdClass;

        $this->repositoryMock->expects($this->once())->method('getDocumentName')->willReturn(\stdClass::class);
        $this->repositoryMock->expects($this->never())->method('find');

        $this->assertSame($entity, $this->target->filter($entity));

    }

    public function invalidValuesProvider()
    {
        $invalidObject = new \DateTime();

        return [
            [['invalid']],
            [$invalidObject],
            [true],
            [1234],
        ];
    }

    /**
     * @dataProvider invalidValuesProvider
     *
     * @param $value
     */
    public function testFilterThrowsExceptionOnInvalidValues($value)
    {
        $this->repositoryMock->expects($this->never())->method('find');
        $this->repositoryMock->expects($this->exactly(2))->method('getDocumentName')->willReturn('testEntityClass');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value must either be a string or an instance of \MongoId or testEntityClass');

        $this->target->filter($value);
    }
}
