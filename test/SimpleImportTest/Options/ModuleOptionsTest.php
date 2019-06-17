<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

namespace SimpleImportTest\Options;

use Cross\TestUtils\TestCase\TestSetterAndGetterTrait;
use Cross\TestUtils\TestCase\SetupTargetTrait;
use SimpleImport\Options\ModuleOptions;

/**
 * Class ModuleOptionsTest
 *
 * @author  Carsten Bleek <bleek@cross-solution.de>
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since   0.30
 * @covers  SimpleImport\Options\ModuleOptions
 * @package SimpleImport\Options
 */
class ModuleOptionsTest extends \PHPUnit_Framework_TestCase
{
    use TestSetterAndGetterTrait, SetupTargetTrait;

    private $target = ModuleOptions::class;

    public function setterAndGetterData()
    {
        return [
            ['importRunDelay', [
                'value' => '60',
                'default' => '1440'
            ]],
            ['geocodeLocale', [
                'value' => 'it',
                'default' => 'de'
            ]],
            ['geocodeRegion', [
                'value' => 'IT',
                'default' => 'DE'
            ]],
            ['geocodeUseSsl', [
                'value' => false,
                'default' => true
            ]],
            ['geocodeGoogleApiKey', [
                'value' => 'apiKey',
                'default' => null
            ]],
            ['classifications', [
                'value' => 'apiKey',
                    'default' => [
                        'professions',
                        'industries',
                        'employmentTypes'
                    ]
            ]],
        ];
    }
}
