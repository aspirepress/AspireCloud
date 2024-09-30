<?php

declare(strict_types=1);

namespace AspirePress\Cdn\V1\CatchAll\Factories;

use AspirePress\Cdn\V1\CatchAll\Handlers\CatchAllHandler;
use Laminas\ServiceManager\ServiceManager;

class CatchAllHandlerFactory
{
    public function __invoke(ServiceManager $serviceManager): CatchAllHandler
    {
        return new CatchAllHandler();
    }
}
