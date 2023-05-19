<?php

declare(strict_types=1);

return [
    /*
     * Logging Configuration
     */
    'logging' => [
        'path' => './logs/',
        'error' => [
            'file' => 'error.log',
            'level' => \Monolog\Level::Debug,
        ],
    ],

    /*
     * Plates Configuration
     */
    'templates' => [
        'extension' => 'php',
        'paths' => [
            'app' => './templates/app',
        ],
    ],
];
