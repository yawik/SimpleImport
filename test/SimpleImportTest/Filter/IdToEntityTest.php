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
use Cross\TestUtils\TestCase\TestInheritanceTrait;
use Doctrine\ODM\MongoDB\DocumentRepository;
use SimpleImport\Filter\IdToEntity;
use Laminas\Filter\FilterInterface;

/**
 * Tests for \SimpleImport\Filter\IdToEntity
 *
 * @covers \SimpleImport\Filter\IdToEntity
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 *
 */
class IdToEntityTest extends TestCase
{
    use TestInheritanceTrait, SetupTargetTrait;

    /**
     *
     *
     * @var array|\PHPUnit_Framework_MockObject_MockObject|IdToEntity|\ReflectionClass
     */
    private $target = [
        'default' => [
            'target' => IdToEntity::class,
            'arguments' => ['@setupRepositoryMock']
        ],
        'create' => [
            [
                'for' => 'testInheritance',
                'reflection' => true
            ],
            [
                'for' => ['testSetterAndGetter', 'testInvokation'],
                'callback' => 'createSimpleTarget',
            ],

        ],
    ];

    private $inheritance = [ FilterInterface::class ];

    /**
     *
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|DocumentRepository
     */
    private $repositoryMock;

    private function setupRepositoryMock()
    {
        $this->repositoryMock = $this->getMockBuilder(DocumentRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['find', 'getDocumentName'])
            ->getMock();

        return $this->repositoryMock;
    }

    public function createSimpleTarget()
    {
        return new class extends IdToEntity
        {
            public $value;
            public function __construct()
            { }

            public function filter($value)
            {
                $this->value = $value;
            }
        };

    }
    /**
     * @testdox Invokation proxies to filter
     */
    public function testInvokation()
    {
        $value = 'testId';
        $this->target->__invoke($value);

        static::assertEquals($value, $this->target->value);
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
