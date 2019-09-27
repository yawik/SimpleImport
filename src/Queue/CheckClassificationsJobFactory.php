<?php declare(strict_types=1);
/**
 * YAWIK SimpleImport
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

namespace SimpleImport\Queue;

use Interop\Container\ContainerInterface;
use SimpleImport\Listener\SolrJobEventListenerFacade;

/**
 * Factory for \SimpleImport\Queue\CheckClassificationsJob
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class CheckClassificationsJobFactory
{
    public function __invoke(
        ContainerInterface $container,
        ?string $requestedName = null,
        ?array $options = null
    ): CheckClassificationsJob {
        $repositories = $container->get('repositories');
        return new CheckClassificationsJob(
            $repositories->get('Jobs'),
            $repositories->get('Jobs/Category'),
            $container->get(SolrJobEventListenerFacade::class)
        );
    }
}
