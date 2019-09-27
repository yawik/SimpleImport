<?php declare(strict_types=1);
/**
 * YAWIK SimpleImport
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

namespace SimpleImport\Listener;

use Interop\Container\ContainerInterface;

/**
 * Factory for \SimpleImport\Listener\SolrJobEventListenerFacade
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class SolrJobEventListenerFacadeFactory
{
    public function __invoke(
        ContainerInterface $container,
        ?string $requestedName = null,
        ?array $options = null
    ): SolrJobEventListenerFacade {
        return new SolrJobEventListenerFacade(
            $container->has('Solr/Listener/JobEventSubscriber')
            ? $container->get('Solr/Listener/JobEventSubscriber')
            : null
        );
    }
}
