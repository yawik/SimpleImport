<?php

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
                        'route' => 'simpleimport add-crawler --name= --feed-uri= [--type=]',
                        'defaults' => [
                            'controller' => 'SimpleImport/ConsoleController',
                            'action' => 'addCrawler',
                            'type' => 'job'
                        ],
                        'constraints' => [
                            'type' => 'job'
                        ]
                    ]
                ]
            ]
        ]
    ],
    'simple_import_crawler_processor_manager' => [
        'factories' => [
            'job' => SimpleImport\Factory\CrawlerProcessor\JobProcessor::class
        ]
    ],
];
