<?php

declare(strict_types=1);

namespace AspirePress\AspireCloud\V1\CatchAll\Factories;

use AspirePress\AspireCloud\V1\CatchAll\Handlers\CatchAllHandler;
use Aura\Sql\ExtendedPdoInterface;
use Laminas\ServiceManager\ServiceManager;

class CatchAllHandlerFactory
{
    public function __invoke(ServiceManager $serviceManager): CatchAllHandler
    {
        $pdo = $serviceManager->get(ExtendedPdoInterface::class);
        return new CatchAllHandler($pdo);
    }
}
