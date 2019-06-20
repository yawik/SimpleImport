<?php

return [
    'doctrine' =>[
        'connection' =>[
            'odm_default' =>[
                'connectionString' => 'mongodb://localhost:27017/YAWIK_TEST',
            ]
        ],
        'configuration' => [
            'odm_default' => [
                'default_db'    => 'YAWIK_TEST',
                'proxy_dir'     => __DIR__.'/../../sandbox/var/cache/DoctrineMongoODMModule/Proxy',
                'hydrator_dir'  => __DIR__.'/../../sandbox/var/cache/DoctrineMongoODMModule/Hydrator',
            ]
        ],
    ],
    'core_options' => [

    ],
    'SimpleImport/Log' => [
        'writers' => [
            [
                'name' => 'stream',
                'options' => [
                    'stream' => realpath(__DIR__.'/../../sandbox').'/var/log/simple-import.log'
                ]
            ]
        ]
    ]
];
