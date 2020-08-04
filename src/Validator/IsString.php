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

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_STRING => "Expected input to be of type string.",
    ];

    public function isValid($value)
    {
        if (is_string($value)) {
            return true;
        }

        $this->error(self::NOT_STRING, $value);
        return false;
    }
}
