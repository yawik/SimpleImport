<?php

/**
 * YAWIK SimpleImport
 *
 * @see        for the canonical source repository
 * @copyright /blob/master/COPYRIGHT
 * @license   /blob/master/LICENSE MIT
 */

declare(strict_types=1);

namespace SimpleImportTest\Validator;

use Cross\TestUtils\TestCase\SetupTargetTrait;
use Cross\TestUtils\TestCase\TestInheritanceTrait;
use Laminas\Validator\AbstractValidator;
use PHPUnit\Framework\TestCase;
use SimpleImport\Validator\IsString;

/**
 * Testcase for \SimpleImport\Validator\IsString
 *
 * @covers \SimpleImport\Validator\IsString
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @group
 */
class IsStringTest extends TestCase
{
    use TestInheritanceTrait, SetupTargetTrait;

    private $target = [
        'create' => [
            [
                'for' => [
                    'testReturnsTrueIfValueIsStringOrNull',
                    'testReturnsFalseIfValueIsNotAStringOrNull',
                    'testProducesErrorMessageOnFailureWithAllowNull'
                ],
                'target' => IsString::class,
                'arguments' => [['allowNull' => true]],
            ],
            [
                'target' => IsString::class
            ],
        ]
    ];

    private $inheritance = [ AbstractValidator::class ];

    public function testReturnsTrueIfValueIsString()
    {
        static::assertTrue($this->target->isValid('thisisastring'));
    }

    public function provideNotStringData()
    {
        return [
            [true],
            [1234],
            [['an', 'array']],
            'assoc' => [['one' => 1, 'two' => 2]],
            [new \stdClass],
            [null]
        ];
    }

    /**
     * @dataProvider provideNotStringData
     */
    public function testReturnsFalseIfValueIsNotString($value)
    {
        static::assertFalse($this->target->isValid($value));
    }

    public function testProducesErrorMessageOnFailure()
    {
        $expect = 'Expected input to be of type string.';

        $this->target->isValid(false);
        $messages = $this->target->getMessages();

        static::assertIsArray($messages);
        static::assertArrayHasKey(IsString::NOT_STRING, $messages);
        static::assertEquals($expect, $messages[IsString::NOT_STRING]);
    }

    public function testReturnsTrueIfValueIsStringOrNull()
    {
        static::assertTrue($this->target->isValid('thisisastring'), 'String value returns false!');
        static::assertTrue($this->target->isValid(null), 'null value returns false');
    }

    public function provideNotStringOrNullData()
    {
        return [
            [true],
            [1234],
            [['an', 'array']],
            'assoc' => [['one' => 1, 'two' => 2]],
            [new \stdClass]
        ];
    }

    /**
     * @dataProvider provideNotStringOrNullData
     */
    public function testReturnsFalseIfValueIsNotStringOrNull($value)
    {
        static::assertFalse($this->target->isValid($value));
    }

    public function testProducesErrorMessageOnFailureWithAllowNull()
    {
        $expect = 'Expected input to be of type string or null.';

        $this->target->isValid(false);
        $messages = $this->target->getMessages();

        static::assertIsArray($messages);
        static::assertArrayHasKey(IsString::NOT_STRING_OR_NULL, $messages);
        static::assertEquals($expect, $messages[IsString::NOT_STRING_OR_NULL]);
    }
}
