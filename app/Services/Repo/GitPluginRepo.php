<?php

declare(strict_types=1);

namespace App\Services\Repo;

use App\Contracts\Repo\PluginRepo;
use App\Data\Props\PluginProps;
use App\Models\WpOrg\Plugin;
use Illuminate\Support\Str;

class GitPluginRepo implements PluginRepo
{
    public function origin(): string
    {
        return 'git';
    }

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
        string $repository_url,
        array $extra = [],
    ): Plugin {
        $now = now();

        $trunc = fn(string $str, int $len = 150) => Str::substr($str, 0, 255);

        return Plugin::create(
            PluginProps::make(
                slug: $trunc($slug),
                name: $trunc($name),
                short_description: $trunc($short_description, 150),
                description: $trunc($description, 1024 * 16),
                version: Str::substr($version, 0, 255),
                author: Str::substr($author, 0, 255),
                requires: $requires,
                tested: $tested,
                download_link: $download_link,
                added: $now,
                last_updated: $now,
                extra: [
                    'ac_created' => $now,
                    'ac_origin' => $this->origin(),
                    'repository_url' => $repository_url,
                    ...$extra,
                ],
            ),
        );
    }
}
