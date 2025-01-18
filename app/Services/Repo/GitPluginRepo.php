<?php

declare(strict_types=1);

namespace App\Services\Repo;

use App\Contracts\Repo\PluginRepo;
use App\Models\WpOrg\Plugin;

class GitPluginRepo implements PluginRepo
{
    public function origin(): string
    {
        return 'git';
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function createPlugin(array $attributes): Plugin
    {
        $slug = $attributes['slug'];
        $name = $attributes['name'];
        $short_description = $attributes['short_description'];
        $description = $attributes['description'];
        $version = $attributes['version'];
        $author = $attributes['author'];
        $requires = $attributes['requires'];
        $tested = $attributes['tested'];
        $download_link = $attributes['download_link'];
        $added = $attributes['added'];
        $ac_origin = $this->origin();

        return Plugin::create(
            compact(
                'slug',
                'name',
                'short_description',
                'description',
                'version',
                'author',
                'requires',
                'tested',
                'download_link',
                'added',
                'ac_origin',
            ),
        );
    }
}

//     slug
//     name
//     short_description
//     description
//     version
//     author
//     requires
//     tested
//     download_link
//     added
