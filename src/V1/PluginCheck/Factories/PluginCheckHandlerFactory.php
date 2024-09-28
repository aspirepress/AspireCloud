<?php

namespace AspirePress\Cdn\V1\PluginCheck\Factories;

use AspirePress\Cdn\Data\Repositories\PluginRepository;
use AspirePress\Cdn\V1\PluginCheck\Handlers\PluginCheckHandler;
use Aura\Sql\ExtendedPdoInterface;
use Laminas\ServiceManager\ServiceManager;
use PDO;

class PluginCheckHandlerFactory
{
    public function __invoke(ServiceManager $serviceManager): PluginCheckHandler
    {
        $repo = $serviceManager->get(PluginRepository::class);
        return new PluginCheckHandler($repo);
    }
}