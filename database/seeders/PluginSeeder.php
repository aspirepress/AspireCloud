<?php

namespace Database\Seeders;

use App\Models\WpOrg\Plugin;
use App\Services\Repo\BarePluginRepo;
use Illuminate\Database\Seeder;

class PluginSeeder extends Seeder
{
    public function __construct(private BarePluginRepo $gitPluginRepo) {}

    public function run(): void
    {
        $this->createGitPlugins();
    }

    private function createGitPlugins(): void
    {
        if (Plugin::query()->where('slug', 'aspireupdate')->where('ac_origin', 'git')->exists()) {
            return;
        }

        $au_version = '0.5';
        $this->gitPluginRepo->createPlugin(
            slug: 'aspireupdate',
            name: 'AspireUpdate',
            short_description: 'A plugin that allows for rewriting the URLs used to fetch updates from WordPress.org to some other endpoint',
            description: 'A plugin that allows for rewriting the URLs used to fetch updates from WordPress.org to some other endpoint',
            version: $au_version,
            author: 'AspirePress',
            requires: '5.3',
            tested: '6.7',
            download_link: "https://github.com/aspirepress/aspireupdate/archive/refs/tags/$au_version.zip",
            extra: [
                'repository_url' => 'https://github.com/aspirepress/aspireupdate',
                'homepage' => 'https://aspirepress.org/',
                'requires_php' => '7.4',
            ],
        );
    }
}
