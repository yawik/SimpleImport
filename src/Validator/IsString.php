<?php

/**
 * YAWIK SimpleImport
 *
 * @copyright 2013-2020 Cross Solution
 * @license   MIT
 */

declare(strict_types=1);

namespace SimpleImport\Validator;

use Laminas\Validator\AbstractValidator;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class IsString extends AbstractValidator
{
    /**
     * @var string
     */
    const NOT_STRING = 'notString';
    const NOT_STRING_OR_NULL = 'notStringOrNull';

    private $allowNull = false;

    public function setAllowNull(bool $flag)
    {
        $this->allowNull = $flag;
    }

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_STRING => "Expected input to be of type string.",
        self::NOT_STRING_OR_NULL => 'Expected input to be of type string or null.',
    ];

    public function isValid($value)
    {
        if (is_string($value) || ($this->allowNull && $value === null)) {
            return true;
        }

        $this->error(
            $this->allowNull ? self::NOT_STRING_OR_NULL : self::NOT_STRING,
            $value
        );

        return false;
    }
}
