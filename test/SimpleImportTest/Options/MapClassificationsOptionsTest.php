<?php declare(strict_types=1);
/**
 * YAWIK SimpleImport
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

namespace SimpleImportTest\Options;

use Cross\TestUtils\TestCase\TestInheritanceTrait;
use Cross\TestUtils\TestCase\TestSetterAndGetterTrait;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use SimpleImport\Options\MapClassificationsOptions;
use Laminas\Stdlib\AbstractOptions;

/**
 * Testcase for \SimpleImport\Options\MapClassificationsOptions
 *
 * @covers \SimpleImport\Options\MapClassificationsOptions
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @group SimpleImport
 * @group SimpleImport.Options
 * @group SimpleImport.Options.MapClassificationsOptions
 */
class MapClassificationsOptionsTest extends TestCase
{
    use TestInheritanceTrait, TestSetterAndGetterTrait;

    private $target = MapClassificationsOptions::class;

    private $inheritance = [ AbstractOptions::class ];

    public function setterAndGetterData(): array
    {
        return [
            ['professions', ['value' => ['map' => 'mapped']]],
            ['industries', ['value' => ['map' => 'mapped']]],
            ['employmentTypes', ['value' => ['map' => 'mapped']]],

            /* Test getMap throws an exception when an unknown type is passed */
            ['map', ['setter' => false, 'getter' => ['getMap', ['unknown']], 'exception' => [OutOfBoundsException::class, 'Unknown map type']]],

            /* Test getMap returns the map with lowercased keys */
            ['map', [
                'setter' => 'setIndustries',            /* set the industries map */
                'value' => ['MAP' => 'mapped'],         /* with that value */
                'getter' => ['getMap', ['industries']], /* Use the getter 'getMap', passing the argument 'industries' */
                'expect' => ['map' => 'mapped'],        /* The array key must be lowercased */
            ]],
        ];
    }

    public function testGetMapsReturnsAnArrayOfMaps()
    {
        $target = new MapClassificationsOptions([
            'industries' => [
                'MAP' => 'mapped',
            ],
            'professions' => [
                'MAP' => 'mapped',
            ],
            'employmentTypes' => [
                'MAP' => 'mapped',
            ],
        ]);

        $expect = [
            'industries' => ['map' => 'mapped'],
            'professions' => ['map' => 'mapped'],
            'employmentTypes' => ['map' => 'mapped'],
        ];

        static::assertEquals($expect, $target->getMaps(['industries', 'professions', 'employmentTypes']));
    }
}
