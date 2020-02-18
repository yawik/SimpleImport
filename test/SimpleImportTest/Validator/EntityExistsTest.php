<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2018 Cross Solution <http://cross-solution.de>
 */

/** */
namespace SimpleImportTest\Validator;

use PHPUnit\Framework\TestCase;

use Cross\TestUtils\TestCase\SetupTargetTrait;
use Cross\TestUtils\TestCase\TestInheritanceTrait;
use Cross\TestUtils\TestCase\TestSetterAndGetterTrait;
use SimpleImport\Validator\EntityExists;
use Laminas\Validator\AbstractValidator;

/**
 * Tests for \SimpleImport\Validator\EntityExists
 *
 * @covers \SimpleImport\Validator\EntityExists
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 *
 */
class EntityExistsTest extends TestCase
{
    use TestInheritanceTrait, TestSetterAndGetterTrait, SetupTargetTrait;

    /**
     *
     *
     * @var array|\ReflectionClass|EntityExists|null
     */
    private $target = [
        'default' => [
            'target' => EntityExists::class
        ],
        'create' => [
            [
                'for' => 'testInheritance',
                'reflection' => true,
            ],
            [
                'for' => 'testConstruct',
                'target' => false
            ],
        ],
    ];

    private $inheritance = [ AbstractValidator::class ];

    private function getSetterAndGetterTarget()
    {
        return new class extends EntityExists
        {
            public function getEntityClass()
            {
                return $this->entityClass;
            }
        };
    }

    public function setterAndGetterData()
    {
        $object = new \stdClass;

        return [
            ['entityClass', ['value' => ['invalid'], 'exception' => [\InvalidArgumentException::class, 'Entity class must be given']]],
            ['entityClass', ['value' => new \stdClass, 'assert' => function($v, $a) { static::assertEquals(\stdClass::class, $a); }]],
            ['entityClass', 'entityClass'],
        ];
    }

    public function optionsProvider()
    {
        return [
            [null],
            ['entityClass'],
            [['entityClass' => 'entityClass']],
        ];
    }

    /**
     * @dataProvider optionsProvider
     *
     * @param $options
     * @covers \SimpleImport\Validator\EntityExists::__construct
     */
    public function testConstruct($options)
    {
        $actual = new EntityExists($options);

        $this->assertInstanceOf(EntityExists::class, $actual);
    }

    public function testIsValidThrowsExceptionIfNoEntityClassIsSet()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('entity class must be set');

        $this->target->isValid('testId');
    }

    public function testIsValidReturnsTrue()
    {
        $this->target->setEntityClass(\stdClass::class);

        $this->assertTrue($this->target->isValid(new \stdClass));
    }

    public function invalidValuesProvider()
    {
        return [
            [1234],
            [new \stdClass],
            [new EntityWithToStringMethod],
        ];
    }

    /**
     * @dataProvider invalidValuesProvider
     *
     * @param string|object $value
     */
    public function testIsValidReturnsFalseAndSetErrorMessage($value = 'Fehler')
    {
        $this->target->setEntityClass('NonExistentClass');

        if (is_object($value)) {
            $expectedValue = method_exists($value, '__toString') ? (string) $value : '[object]';
        } else {
            $expectedValue = (string) $value;
        }

        $this->assertFalse($this->target->isValid($value));
        $messages = $this->target->getMessages();

        $this->assertArrayHasKey(EntityExists::NOT_EXIST, $messages);
        $this->assertStringContainsString("NonExistentClass with ID '$expectedValue'", $messages[EntityExists::NOT_EXIST]);
    }


}

class EntityWithToStringMethod
{
    public function __toString()
    {
        return 'string';
    }
}
