<?php declare(strict_types=1);
/**
 * YAWIK SimpleImport
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

namespace SimpleImportTest\Filter;

use Cross\TestUtils\TestCase\ContainerDoubleTrait;
use Cross\TestUtils\TestCase\CreateProphecyTrait;
use PHPUnit\Framework\TestCase;
use SimpleImport\Filter\MapClassificationsFilter;
use SimpleImport\Filter\MapClassificationsFilterFactory;
use SimpleImport\Options\MapClassificationsOptions;

/**
 * Testcase for \SimpleImport\Filter\MapClassifactionsFilterFactory
 *
 * @covers \SimpleImport\Filter\MapClassificationsFilterFactory
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @group SimpleImport
 * @group SimpleImport.Filter
 * @group SimpleImport.Filter.MapClassificationsFilterFactory
 */
class MapClassificationsFilterFactoryTest extends TestCase
{
    use ContainerDoubleTrait, CreateProphecyTrait;

    public function testCreatesFilterInstance()
    {
        $filterOptions = new MapClassificationsOptions([
            'industries' => [

            ],
            'professions' => [

            ],
            'employmentTypes' => [

            ]
        ]);

        $options = [
            'availableClassifications' => [
                'industries',
                'professions',
                'employmentTypes',
            ],
        ];

        $container = $this->createContainerDouble(
            [
                MapClassificationsOptions::class => [$filterOptions, 1]
            ],
            [
                'target' => \Interop\Container\ContainerInterface::class
            ]
        );

        $target = new MapClassificationsFilterFactory();

        $instance = $target($container, 'irrelevant', $options);

        static::assertInstanceOf(MapClassificationsFilter::class, $instance);


    }

    public function testCreatesFilterInstanceWithOptions()
    {
        $options = [
            'availableClassifications' => [
                'industries',
            ],
        ];

        $filterOptions = $this->createDouble(
            MapClassificationsOptions::class,
            [
                ['getMaps' => [$options['availableClassifications']], 'willReturn' => [['industries' => []]]]
            ]
        );

        $container = $this->createContainerDouble(
            [
                MapClassificationsOptions::class => [$filterOptions, 1]
            ],
            [
                'target' => \Interop\Container\ContainerInterface::class
            ]
        );

        $target = new MapClassificationsFilterFactory();

        $instance = $target($container, 'irrelevant', $options);

    }

    public function testCreatesFilterInstanceWithoutOptions()
    {
        $options = [];

        $filterOptions = $this->createDouble(
            MapClassificationsOptions::class,
            [
                ['getMaps' => [[]], 'willReturn' => [[]]]
            ]
        );

        $container = $this->createContainerDouble(
            [
                MapClassificationsOptions::class => [$filterOptions, 1]
            ],
            [
                'target' => \Interop\Container\ContainerInterface::class
            ]
        );

        $target = new MapClassificationsFilterFactory();

        $instance = $target($container, 'irrelevant', $options);

    }
}
