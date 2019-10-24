<?php

/**
 * YAWIK SimpleImport
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

declare(strict_types=1);
namespace SimpleImport\Filter;

use Interop\Container\ContainerInterface;

/**
 * Factory for \SimpleImport\Filter\ShufflePublishDateFilter
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class ShufflePublishDateFilterFactory
{
    public function __invoke(
        ContainerInterface $container,
        ?string $requestedName = null,
        ?array $options = null
    ): ShufflePublishDateFilter {
        return new ShufflePublishDateFilter(
            $container->get('SimpleImport/Options/Module')->getShuffleInterval()
        );
    }
}
