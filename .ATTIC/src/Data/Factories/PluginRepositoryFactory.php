<?php

declare(strict_types=1);

namespace AspirePress\AspireCloud\Data\Factories;

use AspirePress\AspireCloud\Data\Repositories\PluginRepository;
use Aura\Sql\ExtendedPdoInterface;
use Laminas\ServiceManager\ServiceManager;

class PluginRepositoryFactory
{
    public function __invoke(ServiceManager $serviceManager): PluginRepository
    {
        $pdo = $serviceManager->get(ExtendedPdoInterface::class);
        return new PluginRepository($pdo);
    }
}
