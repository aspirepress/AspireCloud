<?php

declare(strict_types=1);

namespace App;

use App\LoggingListenerDelegatorFactory;
use Laminas\Stratigility\Middleware\ErrorHandler;

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
                'factories' => [
                    TestPage::class => TestPageFactory::class,

                    // Logging Config
                    'error' => LoggingFactory::class,
                ]
            ]
        ];
    }
}
