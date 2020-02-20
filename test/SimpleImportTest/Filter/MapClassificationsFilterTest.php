<?php declare(strict_types=1);
/**
 * YAWIK SimpleImport
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

namespace SimpleImportTest\Filter;

use Cross\TestUtils\TestCase\TestInheritanceTrait;
use PHPUnit\Framework\TestCase;
use SimpleImport\Filter\MapClassificationsFilter;

/**
 * Testcase for \SimpleImport\Filter\MapClassificationsFilter
 *
 * @covers \SimpleImport\Filter\MapClassificationsFilter
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @group SimpleImport
 * @group SimpleImport.Filter
 * @group SimpleImport.Filter.MapClassificationsFilter
 */
class MapClassificationsFilterTest extends TestCase
{
    use TestInheritanceTrait;

    private $inheritance = [
        'target' => MapClassificationsFilter::class,
        \Laminas\Filter\FilterInterface::class,
    ];

    public function testDoesNotFilterNonArray()
    {
        $target = new MapClassificationsFilter([]);
        $value = 'non array';

        static::assertEquals($value, $target->filter($value));
    }

    public function testDoesNotFilterValuesWithoutMaps()
    {
        $target = new MapClassificationsFilter(
            [
                'mapMe' => [
                    'test' => 'TEST',
                ],
            ]
        );

        $value = [
            'doNotMapMe' => [
                'test' => 'unmapped',
            ],
        ];

        static::assertEquals($value, $target->filter($value));
    }

    public function testMapsValues()
    {
        $target = new MapClassificationsFilter(
            [
                'mapMe' => [
                    'test' => 'MAPPED',
                    'map'  => 'MAPPED',
                    'mapmultiple' => ['MultiOne', 'MultiTwo'],
                ],
                'mapMeAlso' => [
                    'foobar' => 'BARFOO',
                ],
            ]
        );

        $value = [
            'mapMe' => [
                'Test', 'Foo', 'maP', 'Map', 'mapmulTiplE', 'bar',
            ],
            'mapMeAlso' => [
                'test', 'Test', 'fOoBAr',
            ]
        ];

        $expect = [
            'mapMe' => [
                'MAPPED', 'Foo', 'MultiOne', 'MultiTwo', 'bar',
            ],
            'mapMeAlso' => [
                'test', 'Test', 'BARFOO',
            ],
        ];

        static::assertEqualsCanonicalizing($expect, $target->filter($value));
    }
}
