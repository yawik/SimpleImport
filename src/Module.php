<?php declare(strict_types=1);
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license   MIT
 * @author    @author Carsten Bleek <bleek@cross-solution.de>
 */

namespace SimpleImport;

use Core\ModuleManager\Feature\VersionProviderInterface;
use Core\ModuleManager\Feature\VersionProviderTrait;
use Core\ModuleManager\ModuleConfigLoader;
use SimpleImport\Controller\CheckClassificationsConsoleController;
use SimpleImport\Controller\GuessLanguageConsoleController;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\ModuleManager\Feature\DependencyIndicatorInterface;

/**
 * Bootstrap module
 */
class Module implements
    DependencyIndicatorInterface,
    ConsoleBannerProviderInterface,
    ConsoleUsageProviderInterface,
    VersionProviderInterface
{

    use VersionProviderTrait;

    const VERSION = '0.4.0';

    /**
     * Loads module specific configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return ModuleConfigLoader::load(__DIR__ . '/../config');
    }

    /**
     * {@inheritDoc}
     * @see DependencyIndicatorInterface::getModuleDependencies()
     */
    public function getModuleDependencies()
    {
        return ['Jobs'];
    }

    public function getConsoleBanner(Console $console)
    {
        return sprintf("%s (%s) %s", __NAMESPACE__, $this->getName(), $this->getVersion());
    }


    /**
     * {@inheritDoc}
     * @see ConsoleUsageProviderInterface::getConsoleUsage()
     */
    public function getConsoleUsage(Console $console)
    {
        return array_merge(
            [
                'Simple import operations',
                '',
                'simpleimport import [--limit] [--name] [--id]'  => 'Executes a data import for all registered crawlers',
                'simpleimport add-crawler' => 'Adds a new import crawler',
                ['--limit=INT', 'Number of crawlers to check per run. Default 3. 0 means no limit'],
                ['--name=STRING', 'The name of a crawler'],
                ['--id=STRING', 'The Mongo object id of a crawler'],
                ['--organization==STRING', 'The ID of an organization'],
                ['--feed-uri=STRING', 'The URI pointing to a data to import'],
                ['--runDelay=INT', 'The number of minutes the next import run will be proceeded again'],
                ['--type=STRING', 'The type of an import (e.g. job)'],
                ['--jobInitialState=STRING', 'The initial state of an imported job'],
                ['--jobRecoverState=STRING', 'The state a job gets, if it was deleted, but found again in later runs.'],
                '',
                'simpleimport info' => 'Displays a list of all available crawlers.',
                'simpleimport info [--id] <name>' => 'Shows information for a crawler',
                'simpleimport update-crawler [--id] <name> [--rename] [--limit] [--organization] [--feed-uri] [--runDelay] [--type] [--jobInitalState] [--jobRecoverState]'
                     => 'Updates configuration for a crawler. ',
                'simpleimport delete-crawler [--id] <name>' => 'Deletes an import crawler',
                ['<name>', 'The name of the crawler to delete.'],
                ['--id', 'Treat <name> as the MongoID of the crawler'],
                ['--rename=STRING', 'Set a new name for the crawler.'],
            ],
            GuessLanguageConsoleController::getConsoleUsage(),
            [''],
            CheckClassificationsConsoleController::getConsoleUsage()
        );
    }
}
