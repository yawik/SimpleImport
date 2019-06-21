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
use SimpleImport\Bridge\Geocoder\Factory as GeocoderFactory;

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
            'class' => Options\ModuleOptions::class,
            'options' => [
                'cache' => [
                    'adapter' => [
                        'name' => 'filesystem',
                        'options' => [
                            'cacheDir' => 'var/cache/geocoder',
                        ],
                    ],
                    'plugins' => ['serializer'],
                ],
            ],
        ],
        Options\LanguageGuesserOptions::class => []
    ],
    'service_manager' => [
        'factories' => [
            'SimpleImport/CrawlerProcessorManager' => Factory\CrawlerProcessor\ManagerFactory::class,
            'SimpleImport/Geocoder/Cache' => GeocoderFactory\CacheFactory::class,
            'SimpleImport/Geocoder/Provider' => GeocoderFactory\ProviderFactory::class,
            'SimpleImport/Geocoder/CacheProvider' => GeocoderFactory\CacheProviderFactory::class,
            'SimpleImport/JobGeocodeLocation' => Factory\Job\GeocodeLocationFactory::class,
            Service\LanguageGuesser::class => Service\LanguageGuesserFactory::class,
        ]
    ],
    'controllers' => [
        'factories' => [
            'SimpleImport/ConsoleController' => Factory\Controller\ConsoleControllerFactory::class,
            Controller\DeleteCrawlerConsoleController::class => Factory\Controller\DeleteCrawlerConsoleControllerFactory::class,
            Controller\UpdateCrawlerConsoleController::class => Factory\Controller\UpdateCrawlerConsoleControllerFactory::class,
            Controller\GuessLanguageConsoleController::class => Factory\Controller\GuessLanguageConsoleControllerFactory::class,
        ]
    ],
    'controller_plugins' => [
        'factories' => [
            Controller\Plugin\LoadCrawler::class => Controller\Plugin\LoadCrawlerFactory::class,
        ],
        'aliases' => [
            'siLoadCrawler' => Controller\Plugin\LoadCrawler::class,
        ],
    ],
    'slm_queue' => [
        'queues' => [
            'simpleimport' => [
                'collection' => 'simpleimport.queue',
            ],
        ],
        'worker_strategies' => [
            'queues' => [
                'simpleimport' => [
                    \Core\Queue\Strategy\LogStrategy::class => ['log' => 'Log/SimpleImport/Queue'],
                    \SlmQueue\Strategy\ProcessQueueStrategy::class,
                ],
            ],
        ],
        'queue_manager' => [
            'factories' => [
                'simpleimport' => \Core\Queue\MongoQueueFactory::class,
            ],
        ],
        'job_manager' => [
            'factories' => [
                Queue\GuessLanguageJob::class => Queue\GuessLanguageJobFactory::class,
            ],
        ],
    ],

    'console' => [
        'router' => [
            'routes' => [
                'simpleimport-import' => [
                    'options' => [
                        'route' => 'simpleimport import [--limit=] [--name=] [--id=]',
                        'defaults' => [
                            'controller' => 'SimpleImport/ConsoleController',
                            'action' => 'import',
                            'limit' => '3',
                        ]
                    ]
                ],
                'simpleimport-add-crawler' => [
                    'options' => [
                        'route' => 'simpleimport add-crawler --name= --organization= --feed-uri= [--runDelay=] [--type=] [--jobInitialState=] [--jobRecoverState=]',
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
                'simpleimport-info' => [
                    'options' => [
                        'route' => 'simpleimport info [--id] [<name>]',
                        'defaults' => [
                            'controller' => Controller\UpdateCrawlerConsoleController::class,
                            'action' => 'index'
                        ],
                    ],
                ],
                'simpleimport-update-crawler' => [
                    'options' => [
                        'route' => 'simpleimport update-crawler [--id] <name> [--rename=] [--feed-uri=] [--rundelay=] [--type=] [--jobInitialState=] [--jobRecoverState=] [--organization=]',
                        'defaults' => [
                            'controller' => Controller\UpdateCrawlerConsoleController::class,
                            'action' => 'update'
                        ],
                    ],
                ],
                'simpleimport-guess-language' => [
                    'options' => [
                        'route' => 'simpleimport guess-language [--limit=]',
                        'defaults' => [
                            'controller' => Controller\GuessLanguageConsoleController::class,
                            'action' => 'index',
                        ],
                    ],
                ],
            ],
        ],
    ],
    'log' => [
        'SimpleImport/Log' => [
            'writers' => [
                [
                    'name' => 'stream',
                    'options' => [
                        'stream' => getcwd() . '/var/log/simple-import.log'
                    ]
                ]
            ]
        ],
        'Log/SimpleImport/Queue' => [
            'writers' => [
                [
                    'name' => 'stream',
                    'priority' => 1000,
                    'options' => [
                        'stream' => getcwd().'/var/log/simpleimport.queue.log',
                        'formatter'  => [
                            'name' => 'simple',
                            'options' => [
                                'format' => '%timestamp% (%pid%) %priorityName%: %message% %extra%',
                                'dateTimeFormat' => 'd.m.Y H:i:s',
                            ],
                        ],
                    ],
                ],
            ],
            'processors' => [
                ['name' => \Core\Log\Processor\ProcessId::class],
            ],
        ],
    ],
    'input_filters' => [
        'factories' => [
            InputFilter\CrawlerInputFilter::class => InvokableFactory::class
        ]
    ],
    'filters' => [
        'factories' => [
            Filter\IdToEntity::class => Filter\IdToEntityFactory::class,
        ],
    ],
    'validators' => [
        'factories' => [
            'SimpleImportOrganizationExists' => Factory\Validator\OrganizationExistsFactory::class,
            Validator\CrawlerOptions::class => InvokableFactory::class,
            Validator\EntityExists::class => InvokableFactory::class,
        ],
    ],
    'simple_import_crawler_processor_manager' => [
        'factories' => [
            Crawler::TYPE_JOB => Factory\CrawlerProcessor\JobProcessorFactory::class
        ]
    ],

    'event_manager' => [
        'Core/EntityEraser/Dependencies/Events' => [
            'listeners' => [
                Listener\CheckJobDependencyListener::class => ['*', true ]
            ],
        ]
    ]
];
