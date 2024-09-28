<?php

declare(strict_types=1);

// turn on all errors
use AspirePress\Cdn\Helpers\ContainerHelper;
use AspirePress\Cdn\Helpers\DbHelper;
use Laminas\ServiceManager\ServiceManager;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;

error_reporting(E_ALL);

// autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists('.env')) {
    $dotenv = Dotenv\Dotenv::createUnsafeImmutable('.');
    $dotenv->load();
}

/** @var ServiceManager $container */
$container = require __DIR__ . '/../config/container.php';

/** @var Application $app */
$app     = $container->get(Application::class);
$factory = $container->get(MiddlewareFactory::class);

// Load Routes
(require 'config/routes.php')($app, $factory, $container);

$config                       = $container->get('config');
$dbSchema                     = DbHelper::DB_NAME;
$config['database']['schema'] = $dbSchema;

putenv("DB_SCHEMA={$dbSchema}");
$container->setAllowOverride(true);
$container->setService('config', $config);
$container->setAllowOverride(false);

ContainerHelper::registerContainer($container);
