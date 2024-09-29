<?php

declare(strict_types=1);

namespace AspirePress\Cdn\V1\PluginCheck\Factories;

use AspirePress\Cdn\Data\Repositories\PluginRepository;
use AspirePress\Cdn\V1\PluginCheck\Handlers\PluginCheckHandler;
use Laminas\ServiceManager\ServiceManager;

class PluginCheckHandlerFactory
{
    public function __invoke(ServiceManager $serviceManager): PluginCheckHandler
    {
        $repo = $serviceManager->get(PluginRepository::class);
        return new PluginCheckHandler($repo);
    }
}
