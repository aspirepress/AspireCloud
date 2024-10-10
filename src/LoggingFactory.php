<?php

declare(strict_types=1);

namespace AspirePress\AspireCloud;

use InvalidArgumentException;
use Laminas\ServiceManager\ServiceManager;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;

class LoggingFactory
{
    public function __invoke(ServiceManager $serviceManager, string $serviceName): Logger
    {
        $config = $serviceManager->get('config')['logging'];

        $channel = $config['channel'];

        if (! isset($config['channels'][$channel])) {
            throw new InvalidArgumentException('Unknown channel: ' . $channel);
        }

        if (! isset($config['channels'][$channel][$serviceName])) {
            throw new InvalidArgumentException('Unknown service name: ' . $serviceName);
        }

        $handlerConfig = $config['channels'][$channel][$serviceName];
        $handlers      = [];
        foreach ($handlerConfig as $handlerName) {
            if (! isset($config['handlers'][$handlerName])) {
                throw new InvalidArgumentException('Unknown handler: ' . $handlerName);
            }

            $handlers[] = new $config['handlers'][$handlerName]['handler'](...$config['handlers'][$handlerName]['args']);
        }

        $log = new Logger($serviceName);
        $log->setHandlers($handlers);

        /** @var UidProcessor $uidProcessor */
        $uidProcessor = $serviceManager->get(UidProcessor::class);
        $log->pushProcessor($uidProcessor);

        return $log;
    }
}
