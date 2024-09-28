<?php

declare(strict_types=1);

namespace AspirePress\Cdn\Helpers;

use Psr\Container\ContainerInterface;
use RuntimeException;

class ContainerHelper
{
    private static ContainerInterface $container;

    public static function registerContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }

    public static function getContainer(): ContainerInterface
    {
        if (! isset(self::$container)) {
            throw new RuntimeException('The container is not registered.');
        }

        return self::$container;
    }
}
