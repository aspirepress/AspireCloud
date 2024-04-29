<?php

declare(strict_types=1);

use Monolog\Level;

return [

    /*
     * Database Configuration
     */
    'database' => [
        'type'   => 'pgsql',
        'host'   => getenv('DB_HOST'),
        'name'   => getenv('DB_NAME'),
        'user'   => getenv('DB_USER'),
        'pass'   => getenv('DB_PASS'),
        'schema' => getenv('DB_SCHEMA'),
    ],

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
