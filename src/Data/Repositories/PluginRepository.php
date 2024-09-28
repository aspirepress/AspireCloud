<?php

declare(strict_types=1);

namespace AspirePress\Cdn\Data\Repositories;

use AspirePress\Cdn\Data\Entities\Plugin;
use AspirePress\Cdn\Data\Values\Version;
use Aura\Sql\ExtendedPdoInterface;
use Ramsey\Uuid\Uuid;

class PluginRepository
{
    public function __construct(private ExtendedPdoInterface $epdo)
    {
    }
    public function getPluginBySlug(string $slug): ?Plugin
    {
        $plugin = Plugin::fromValues(
            Uuid::uuid7(),
            'Foo Plugin',
            'foo-plugin',
            Version::fromString('1.2.3.4')
        );

        return $plugin;
    }
}