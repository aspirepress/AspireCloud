<?php

namespace AspirePress\Cdn\Data\Factories;

use AspirePress\Cdn\Data\Repositories\PluginRepository;
use Aura\Sql\ExtendedPdoInterface;
use Laminas\ServiceManager\ServiceManager;

class PluginRepositoryFactory
{
    public function __invoke(ServiceManager $serviceManager): PluginRepository
    {
        $pdo = $serviceManager->get(ExtendedPdoInterface::class);
        return new PluginRepository();
    }
}