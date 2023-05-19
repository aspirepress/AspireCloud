<?php

declare(strict_types=1);

namespace App;

use Laminas\ServiceManager\ServiceManager;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggingFactory
{
    public function __invoke(ServiceManager $serviceManager, string $serviceName): Logger
    {
        $config = $serviceManager->get('config');
        $loggingInfo = $config['logging'];

        if (!isset($loggingInfo[$serviceName])) {
            throw new \InvalidArgumentException('Unknown service name: ' . $serviceName);
        }

        $logConfig = $loggingInfo[$serviceName];

        $log = new Logger($serviceName);
        $log->pushHandler(new StreamHandler($loggingInfo['path'] . $logConfig['file'], $logConfig['level']));
        return $log;
    }
}
