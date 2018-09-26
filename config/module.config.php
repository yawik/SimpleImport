<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */

namespace SimpleImport;

use SimpleImport\Entity\Crawler;
use Zend\ServiceManager\Factory\InvokableFactory;

/**
 * create a config/autoload/SimpleImport.local.php and put modifications there.
 */
return [
    'doctrine' => [
        'driver' => [
            'odm_default' => [
                'drivers' => [
                    'SimpleImport\Entity' => 'annotation'
                ]
            ],
            'annotation' => [
                'paths' => [
                    __DIR__ . '/../src/Entity'
                ]
            ]
        ]
    ],
    'options' => [
        'SimpleImport/Options/Module' => [
            'class' => Options\ModuleOptions::class
        ]
    ],
    'service_manager' => [
        'factories' => [
            'SimpleImport/CrawlerProcessorManager' => Factory\CrawlerProcessor\ManagerFactory::class,
            'SimpleImport/JobGeocodeLocation' => Factory\Job\GeocodeLocationFactory::class
        ]
    ],
    'controllers' => [
        'factories' => [
            'SimpleImport/ConsoleController' => Factory\Controller\ConsoleControllerFactory::class,
            Controller\DeleteCrawlerConsoleController::class => Factory\Controller\DeleteCrawlerConsoleControllerFactory::class,
        ]
    ],
    'console' => [
        'router' => [
            'routes' => [
                'simpleimport-import' => [
                    'options' => [
                        'route' => 'simpleimport import [--limit=]',
                        'defaults' => [
                            'controller' => 'SimpleImport/ConsoleController',
                            'action' => 'import',
                            'limit' => '3'
                        ]
                    ]
                ],
                'simpleimport-add-crawler' => [
                    'options' => [
                        'route' => 'simpleimport add-crawler --name= --organization= --feed-uri= [--runDelay=] [--type=] [--jobInitialState=]',
                        'defaults' => [
                            'controller' => 'SimpleImport/ConsoleController',
                            'action' => 'addCrawler',
                            'type' => 'job'
                        ]
                    ]
                ],
                'simpleimport-delete-crawler' => [
                    'options' => [
                        'route' => 'simpleimport delete-crawler [--id] <name>',
                        'defaults' => [
                            'controller' => Controller\DeleteCrawlerConsoleController::class,
                            'action' => 'index',
                        ],
                    ],
                ],
            ]
        ]
    ],
    'log' => [
        'SimpleImport/Log' => [
            'writers' => [
                [
                    'name' => 'stream',
                    'options' => [
                        'stream' => __DIR__ . '/../../../log/simple-import.log'
                    ]
                ]
            ]
        ]
    ],
    'input_filters' => [
        'factories' => [
            InputFilter\CrawlerInputFilter::class => InvokableFactory::class
        ]
    ],
    'validators' => [
        'factories' => [
            'SimpleImportOrganizationExists' => Factory\Validator\OrganizationExistsFactory::class,
            Validator\CrawlerOptions::class => InvokableFactory::class
        ]
    ],
    'simple_import_crawler_processor_manager' => [
        'factories' => [
            Crawler::TYPE_JOB => Factory\CrawlerProcessor\JobProcessorFactory::class
        ]
    ],
];
