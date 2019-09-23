<?php
chdir(dirname(__DIR__));
return [
    'modules' => [
        'Core',
        'Auth',
        'Jobs',
        'Cv',
        'Applications',
        'Settings',
        'Organizations',
        'SimpleImport',
        'Geo',
        'SlmQueue',
    ],
];
