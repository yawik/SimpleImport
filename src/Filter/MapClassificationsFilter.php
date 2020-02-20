<?php declare(strict_types=1);
/**
 * YAWIK SimpleImport
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

namespace SimpleImport\Filter;

use Laminas\Filter\FilterInterface;
use function array_push;
use function array_unique;
use function is_array;
use function strtolower;

/**
 * Maps classifications to known yawik categories.
 *
 * ```
 * $f = new MapClassificationsFilter(
 *  [
 *      'industries' => ['map' => 'mapped', 'multi' => ['map1', 'map2']],
 *      'professions' => ['map' => 'mapped'],
 *      'employmentTypes' => ['map' => 'mapped'],
 *  ]
 * );
 *
 * $v = [
 *  'industries' => ['Map', 'maP', 'map', 'something', 'multi'],
 * ];
 *
 * $v = $f->filter($v);
 * // $v = [
 * //     'industries' => ['mapped', 'something', 'map1', 'map2']
 * //];
 * ```
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 */
class MapClassificationsFilter implements FilterInterface
{

    /**
     * The maps for 'industries', 'professions' and 'employmentTypes'
     *
     * @var array
     */
    private $maps = [];

    /**
     * @param array $maps
     */
    public function __construct(array $maps)
    {
        $this->maps = $maps;
    }

    /**
     * Filter array of string arrays according to the maps.
     *
     * If _$value_ is not an array of array with string items,
     * the _$value_ is returned unchanged.
     *
     * All items are mapped case insensitive. Duplicate items will be removed.
     *
     * @param mixed $value
     * @return mixed
     */
    public function filter($value)
    {
        if (!is_array($value)) {
            return $value;
        }

        foreach ($value as $name => &$values) {
            if (!isset($this->maps[$name])) {
                continue;
            }

            $values = $this->filterClassification(
                $values,
                $this->maps[$name]
            );
        }

        return $value;
    }

    private function filterClassification(array $values, array $map): array
    {
        $mappedValues = [];
        foreach ($values as $item) {
            $mapped = $map[strtolower($item)] ?? false;
            if ($mapped === false) {
                $mappedValues[] = $item;
                continue;
            }

            if (is_array($mapped)) {
                array_push($mappedValues, ...$mapped);
                continue;
            }

            $mappedValues[] = $mapped;
        }

        return array_unique($mappedValues);
    }
}
