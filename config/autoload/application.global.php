<?php

declare(strict_types=1);

use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TestHandler;
use Monolog\Level;

return [

    /*
     * Database Configuration
     */
    'database' => [
        'type'   => 'pgsql',
        'host'   => $_ENV['DB_HOST'],
        'name'   => $_ENV['DB_NAME'],
        'user'   => $_ENV['DB_USER'],
        'pass'   => $_ENV['DB_PASS'],
        'schema' => $_ENV['DB_SCHEMA'],
    ],

    /*
         * Logging Configuration
         */
    'logging' => [
        'channel'  => $_ENV['LOG_CHANNEL'] ?? 'default',
        'channels' => [
            'default' => [
                'logger' => ['stderr'],
            ],
            'file'    => [
                'logger' => ['file_error', 'stderr'],
            ],
            'test'    => [
                'logger' => ['test'],
            ],
        ],
        'handlers' => [
            'stderr'     => [
                'handler' => ErrorLogHandler::class,
                'args'    => [0, $_ENV['LOG_LEVEL'] ?? Level::Error],
            ],
            'file_error' => [
                'handler' => StreamHandler::class,
                'args'    => ['./logs/error.log', $_ENV['LOG_LEVEL'] ?? Level::Error],
            ],
            'test'       => [
                'handler' => TestHandler::class,
                'args'    => [],
            ],
        ],
    ],
];
