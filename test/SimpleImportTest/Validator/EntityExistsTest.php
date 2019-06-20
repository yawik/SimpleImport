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

use CoreTestUtils\TestCase\TestInheritanceTrait;
use CoreTestUtils\TestCase\TestSetterGetterTrait;
use SimpleImport\Validator\EntityExists;
use Zend\Validator\AbstractValidator;

/**
 * Tests for \SimpleImport\Validator\EntityExists
 * 
 * @covers \SimpleImport\Validator\EntityExists
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 *  
 */
class EntityExistsTest extends TestCase
{
    use TestInheritanceTrait, TestSetterGetterTrait;

    /**
     *
     *
     * @var array|\ReflectionClass|EntityExists|null
     */
    private $target = [
        EntityExists::class,
        '@testInheritance' => [ 'as_reflection' => true ],
        '@testConstruct' => false,
    ];

    private $inheritance = [ AbstractValidator::class ];

    public function propertiesProvider()
    {
        return [
            ['entityClass', ['value' => ['invalid'], 'setter_exception' => [\InvalidArgumentException::class, 'Entity class must be given']]],
            ['entityClass', ['value' => new \stdClass, 'expect_property' => \stdClass::class]],
            ['entityClass', ['value' => 'entityClass', 'expect_property' => 'entityClass']],
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
        $this->assertContains("NonExistentClass with ID '$expectedValue'", $messages[EntityExists::NOT_EXIST]);
    }


}

class EntityWithToStringMethod
{
    public function __toString()
    {
        return 'string';
    }
}
