<?php

declare(strict_types=1);

use Monolog\Level;

return [

    /*
     * Database Configuration
     */
    'database' => [
        'type' => 'pgsql',
        'host' => getenv('DB_HOST'),
        'name' => getenv('DB_NAME'),
        'user' => getenv('DB_USER'),
        'pass' => getenv('DB_PASS'),
        'schema' => getenv('DB_SCHEMA'),
    ],

    /*
         * Logging Configuration
         */
    'logging' => [
        'channel' => $_ENV['LOG_CHANNEL'] ?? 'default',
        'channels' => [
            'default' => [
                'logger' => ['stderr'],
            ],
            'file' => [
                'logger' => ['file_error', 'stderr'],
            ],
            'test' => [
                'logger' => ['test'],
            ],
        ],
        'handlers' => [
            'stderr' => [
                'handler' => \Monolog\Handler\ErrorLogHandler::class,
                'args' => [0, $_ENV['LOG_LEVEL'] ?? Level::Error],
            ],
            'file_error' => [
                'handler' => \Monolog\Handler\StreamHandler::class,
                'args' => ['./logs/error.log', $_ENV['LOG_LEVEL'] ?? Level::Error],
            ],
            'test' => [
                'handler' => \Monolog\Handler\TestHandler::class,
                'args' => [],
            ],
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
