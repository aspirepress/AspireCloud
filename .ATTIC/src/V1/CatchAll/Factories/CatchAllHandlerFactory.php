<?php

declare(strict_types=1);

namespace AspirePress\AspireCloud\V1\CatchAll\Factories;

use AspirePress\AspireCloud\V1\CatchAll\Handlers\CatchAllHandler;
use Laminas\ServiceManager\ServiceManager;

class CatchAllHandlerFactory
{
    public function __invoke(ServiceManager $serviceManager): CatchAllHandler
    {
        return new CatchAllHandler();
    }
}
