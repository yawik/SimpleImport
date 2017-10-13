<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license   MIT
 * @author    @author Carsten Bleek <bleek@cross-solution.de>
 */

namespace SimpleImport;

use Core\ModuleManager\ModuleConfigLoader;
use Zend\ModuleManager\Feature\DependencyIndicatorInterface;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;

/**
 * Bootstrap module
 */
class Module implements DependencyIndicatorInterface, ConsoleUsageProviderInterface
{

    /**
     * Loads module specific configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return ModuleConfigLoader::load(__DIR__ . '/config');
    }

    /**
     * {@inheritDoc}
     * @see DependencyIndicatorInterface::getModuleDependencies()
     */
    public function getModuleDependencies()
    {
        return ['Jobs'];
    }
    
    /**
     * {@inheritDoc}
     * @see ConsoleUsageProviderInterface::getConsoleUsage()
     */
    public function getConsoleUsage(Console $console)
    {
        return [
            'Simple import operations',
            'simpleimport import [--limit]'  => 'Executes a data import for all registered crawlers',
            'simpleimport add-crawler --name --organization= --feed-uri [--type] [--jobInitialState]'  => 'Adds a new import crawler',
            ['--limit=INT', 'Number of crawlers to check per run. Default 3. 0 means no limit'],
            ['--name=STRING', 'The name of a crawler'],
            ['--organization==STRING', 'The ID of an organization'],
            ['--feed-uri=STRING', 'The URI pointing to a data to import'],
            ['--type=STRING', 'The type of an import (e.g. job)'],
            ['--type=jobInitialState', 'The initial state of an imported job'],
        ];
    }
}
