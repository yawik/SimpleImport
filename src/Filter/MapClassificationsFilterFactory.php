<?php declare(strict_types=1);
/**
 * YAWIK SimpleImport
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

namespace SimpleImport\Filter;

use Interop\Container\ContainerInterface;
use SimpleImport\Options\MapClassificationsOptions;

/**
 * Factory for \SimpleImport\Filter\MapClassificationsFilter
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 */
class MapClassificationsFilterFactory
{
    public function __invoke(
        ContainerInterface $container,
        ?string $requestedName = null,
        ?array $options = null
    ): MapClassificationsFilter {
        return new MapClassificationsFilter(
            $container
                ->get(MapClassificationsOptions::class)
                ->getMaps($options['availableClassifications'] ?? [])
        );
    }
}
