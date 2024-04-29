<?php

declare(strict_types=1);

namespace App;

use App\LoggingListenerDelegatorFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\Stratigility\Middleware\ErrorHandler;
use Monolog\Processor\UidProcessor;

class ConfigProvider
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                'delegators' => [
                    ErrorHandler::class => [LoggingListenerDelegatorFactory::class],
                ],
                'factories'  => [
                    TestPage::class => TestPageFactory::class,

                    // Logging Config
                    'logger'            => LoggingFactory::class,
                    UidProcessor::class => InvokableFactory::class,
                ],
            ],
        ];
    }
}
