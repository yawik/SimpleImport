<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

namespace SimpleImport\Options;

use CoreTestUtils\TestCase\TestSetterGetterTrait;
use CoreTestUtils\TestCase\SetupTargetTrait;
use SimpleImport\Options\ModuleOptions;

/**
 * Class ModuleOptionsTest
 *
 * @author  Carsten Bleek <bleek@cross-solution.de>
 * @since   0.30
 * @covers  SimpleImport\Options\ModuleOptions
 * @package SimpleImport\Options
 */
class ModuleOptionsTest extends \PHPUnit_Framework_TestCase
{
    use TestSetterGetterTrait, SetupTargetTrait;

    protected $target = [
        'class' => ModuleOptions::class
    ];

    public function propertiesProvider()
    {
        return [
            ['importRunDelay', [
                'value' => '60',
                'default' => '1440'
            ]],
        ];
    }
}
