<?php

declare(strict_types=1);

namespace App\Services\Repo;

use App\Contracts\Repo\PluginRepo;
use App\Models\WpOrg\Plugin;

class BarePluginRepo implements PluginRepo
{
    public function origin(): string
    {
        return 'bare';
    }

    /**
     * @param array<string, mixed> $extra
     */
    public function createPlugin(
        string $slug,
        string $name,
        string $short_description,
        string $description,
        string $version,
        string $author,
        string $requires,
        string $tested,
        string $download_link,
        array $extra = [],
    ): Plugin {
        $now = now();

        return Plugin::create([
            'slug' => $slug,
            'name' => $name,
            'short_description' => $short_description,
            'description' => $description,
            'version' => $version,
            'author' => $author,
            'requires' => $requires,
            'tested' => $tested,
            'download_link' => $download_link,
            'added' => $now,
            'last_updated' => $now,
            'ac_created' => $now,
            'ac_origin' => $this->origin(),
            'ac_raw_metadata' => [],
            ...$extra,
        ]);
    }
}
