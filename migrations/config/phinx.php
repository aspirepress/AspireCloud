<?php

declare(strict_types=1);

if (file_exists('.env')) {
    $dotenv = Dotenv\Dotenv::createUnsafeImmutable('.');
    $dotenv->load();
}

return [
    'paths'         => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/../db/migrations',
        'seeds'      => '%%PHINX_CONFIG_DIR%%/../db/seeds',
    ],
    'environments'  => [
        'default_migration_table' => 'phinxlog',
        'default_environment'     => 'development',
        'development'             => [
            'adapter' => 'pgsql',
            'host'    => getenv('DB_HOST'),
            'name'    => getenv('DB_NAME'),
            'user'    => getenv('DB_USER'),
            'pass'    => getenv('DB_PASS'),
            'port'    => getenv('DB_PORT'),
            'charset' => 'utf8',
        ],
        'functional_tests'        => [
            'adapter' => 'pgsql',
            'schema'  => 'functional_tests',
            'host'    => getenv('DB_HOST'),
            'name'    => getenv('DB_NAME'),
            'user'    => getenv('DB_USER'),
            'pass'    => getenv('DB_PASS'),
            'port'    => getenv('DB_PORT'),
            'charset' => 'utf8',
        ],
        'acceptance_tests'        => [
            'adapter' => 'pgsql',
            'schema'  => 'public',
            'host'    => getenv('DB_HOST'),
            'name'    => getenv('DB_NAME'),
            'user'    => getenv('DB_USER'),
            'pass'    => getenv('DB_PASS'),
            'port'    => getenv('DB_PORT'),
            'charset' => 'utf8',
        ],
    ],
    'version_order' => 'creation',
];
