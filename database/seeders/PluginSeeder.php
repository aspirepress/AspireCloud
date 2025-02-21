<?php

namespace Database\Seeders;

use App\Models\WpOrg\Plugin;
use App\Services\Repo\BarePluginRepo;
use Illuminate\Database\Seeder;

class PluginSeeder extends Seeder
{
    public function __construct(private readonly BarePluginRepo $gitPluginRepo) {}

    public function run(): void
    {
        $this->createAspireUpdatePlugin();
    }

    private function createAspireUpdatePlugin(): void
    {
        if (Plugin::query()->where('slug', 'aspireupdate')->where('ac_origin', 'git')->exists()) {
            return;
        }

        // abbreviated slightly from https://thefragens.net/wp-json/git-updater/v1/update-api/?slug=aspireupdate
        $metadata = [
            "name" => "AspireUpdate",
            "slug" => "aspireupdate",
            "git" => "github",
            "type" => "plugin",
            "url" => "https://github.com/aspirepress/aspireupdate",
            "is_private" => false,
            "dot_org" => false,
            "release_asset" => false,
            "version" => "0.6.1",
            "author" => "AspirePress",
            "requires" => "5.3",
            "tested" => "6.7",
            "requires_php" => "7.4",
            "requires_plugins" => [],
            "sections" => [
                "description" => "<p>This plugin allows a WordPress user to automatically rewrite certain URLs and URL paths to a new URL. This is helpful because it allows for the rewriting of api.wordpress.org to some other repository that contains the plugins the user wants.</p>
<p>The plugin supports multiple rewrites, and also supports rewriting the URL paths of the requests on a per-host basis. This improves the capacity of the plugin to adequately support newer or different repositories.</p>",
            ],
            "short_description" => "This plugin allows a WordPress user to automatically rewrite certain URLs and URL paths to a new URL. This is helpful because it allows for the rew...",
            "primary_branch" => "main",
            "branch" => "main",
            "download_link" => "https://api.github.com/repos/aspirepress/aspireupdate/zipball/0.6.1",
            // "tags" => [], // FIXME
            "versions" => [
                "0.6.1" => "https://api.github.com/repos/aspirepress/aspireupdate/zipball/0.6.1",
                "0.6" => "https://api.github.com/repos/aspirepress/aspireupdate/zipball/0.6",
                "0.5" => "https://api.github.com/repos/aspirepress/aspireupdate/zipball/0.5",
            ],
            "donate_link" => "https://github.com/sponsors/aspirepress",
            "banners" => [],
            "icons" => ["default" => "https://s.w.org/plugins/geopattern-icon/aspireupdate.svg",],
            "last_updated" => "2025-02-21T19:06:33Z",
            "num_ratings" => 0,
            "rating" => 0,
            "active_installs" => 0,
            "homepage" => "https://aspirepress.org/",
            "external" => "xxx",
        ];

        $this->gitPluginRepo->createPlugin(
            slug: $metadata['slug'],
            name: $metadata['name'],
            short_description: mb_substr($metadata['short_description'], 0, 150),
            description: $metadata['sections']['description'],
            version: $metadata['version'],
            author: $metadata['author'],
            requires: $metadata['requires'],
            tested: $metadata['tested'],
            download_link: $metadata['download_link'],
            extra: $metadata,
        );
    }
}
