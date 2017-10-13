<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */

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
            'class' => SimpleImport\Options\ModuleOptions::class
        ]
    ],
    'service_manager' => [
        'factories' => [
            'SimpleImport/CrawlerProcessorManager' => SimpleImport\Factory\CrawlerProcessor\ManagerFactory::class
        ]
    ],
    'controllers' => [
        'factories' => [
            'SimpleImport/ConsoleController' => SimpleImport\Factory\Controller\ConsoleControllerFactory::class
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
                ]
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
            SimpleImport\InputFilter\CrawlerInputFilter::class => InvokableFactory::class
        ]
    ],
    'validators' => [
        'factories' => [
            'SimpleImportOrganizationExists' => SimpleImport\Factory\Validator\OrganizationExistsFactory::class,
            SimpleImport\Validator\CrawlerOptions::class => InvokableFactory::class
        ]
    ],
    'simple_import_crawler_processor_manager' => [
        'factories' => [
            Crawler::TYPE_JOB => SimpleImport\Factory\CrawlerProcessor\JobProcessorFactory::class
        ]
    ],
];
