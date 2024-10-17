<?php

declare(strict_types=1);

namespace AspirePress\AspireCloud;

use AspirePress\AspireCloud\Data\Factories\PluginRepositoryFactory;
use AspirePress\AspireCloud\Data\Repositories\PluginRepository;
use AspirePress\AspireCloud\Repository\Api\V1\ApiTokenIssuanceHandler;
use AspirePress\AspireCloud\V1\CatchAll\Factories\CatchAllHandlerFactory;
use AspirePress\AspireCloud\V1\CatchAll\Handlers\CatchAllHandler;
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
                'invokables' => [
                    ApiTokenIssuanceHandler::class => ApiTokenIssuanceHandler::class,
                ],
                'delegators' => [
                    ErrorHandler::class => [LoggingListenerDelegatorFactory::class],
                ],
                'factories'  => [
                    PluginRepository::class     => PluginRepositoryFactory::class,
                    ExtendedPdoInterface::class => ExtendedPdoFactory::class,
                    CatchAllHandler::class      => CatchAllHandlerFactory::class,

                    // Logging Config
                    'logger'            => LoggingFactory::class,
                    UidProcessor::class => InvokableFactory::class,
                ],
            ],
        ];
    }
}
