<?php

declare(strict_types=1);

namespace App;

use Aura\Sql\ExtendedPdo;
use Laminas\ServiceManager\ServiceManager;

class ExtendedPdoFactory
{
    public function __invoke(ServiceManager $serviceManager): ExtendedPdo
    {
        $config   = $serviceManager->get('config');
        $database = $config['database'];
        $dsn      = sprintf('%s:host=%s;dbname=%s', $database['type'], $database['host'], $database['name']);
        $pdo      = new ExtendedPdo(
            $dsn,
            $database['user'],
            $database['pass'],
        );
        $pdo->exec('SET search_path TO ' . $config['database']['schema']);
        return $pdo;
    }
}
