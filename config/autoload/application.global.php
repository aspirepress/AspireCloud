<?php

declare(strict_types=1);

use Monolog\Level;

return [
    /*
     * Logging Configuration
     */
    'logging' => [
        'path'  => './logs/',
        'error' => [
            'file'  => 'error.log',
            'level' => Level::Debug,
        ],
    ],

    /*
     * Plates Configuration
     */
    'templates' => [
        'extension' => 'php',
        'paths'     => [
            'app' => './templates/app',
        ],
    ],
];
