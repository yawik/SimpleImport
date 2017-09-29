<?php
use SimpleImport\Factory\Controller as ControllerFactory;

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
    'service_manager' => [
        'factories' => []
    ],
    'controllers' => [
        'factories' => [
            'SimpleImport/ConsoleController' => ControllerFactory\ConsoleControllerFactory::class
        ]
    ],
    'console' => [
        'router' => [
            'routes' => [
                'simpleimport-import' => [
                    'options' => [
                        'route' => 'simpleimport import',
                        'defaults' => [
                            'controller' => 'SimpleImport/ConsoleController',
                            'action' => 'import'
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
    ]
];
