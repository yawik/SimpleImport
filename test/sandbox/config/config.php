<?php
chdir(dirname(__DIR__));
return [
    'modules' => [
        'Core',
        'Cv',
        'Auth',
        'Jobs',
        'Applications',
        'Settings',
        'Organizations',
        'Geo',
        'SimpleImport',
    ],
    'core_options' => [
        'system_message_email' => 'developer@yawik.org',
    ],
];
