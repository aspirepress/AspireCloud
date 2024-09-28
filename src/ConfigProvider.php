<?php

declare(strict_types=1);

namespace AspirePress\Cdn;

use AspirePress\Cdn\Data\Factories\PluginRepositoryFactory;
use AspirePress\Cdn\Data\Repositories\PluginRepository;
use AspirePress\Cdn\LoggingListenerDelegatorFactory;
use AspirePress\Cdn\V1\PluginCheck\Factories\PluginCheckHandlerFactory;
use AspirePress\Cdn\V1\PluginCheck\Handlers\PluginCheckHandler;
use Aura\Sql\ExtendedPdoInterface;
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
                    PluginCheckHandler::class => PluginCheckHandlerFactory::class,

                    PluginRepository::class => PluginRepositoryFactory::class,

                    ExtendedPdoInterface::class => ExtendedPdoFactory::class,

                    // Logging Config
                    'logger'            => LoggingFactory::class,
                    UidProcessor::class => InvokableFactory::class,
                ],
            ],
        ];
    }
}
