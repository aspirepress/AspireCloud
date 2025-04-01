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
        // Plugin::query()->where('slug', 'aspireupdate')->delete();

        if (Plugin::query()->where('slug', 'aspireupdate')->where('ac_origin', 'git')->exists()) {
            return;
        }

        // from https://thefragens.net/wp-json/git-updater/v1/update-api/?slug=aspireupdate
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
            "contributors" => [
                "sarah-savage" => [
                    "display_name" => "sarah-savage",
                    "profile" => "//profiles.wordpress.org/sarah-savage",
                    "avatar" => "https://wordpress.org/grav-redirect.php?user=sarah-savage",
                ],
                "namithj" => [
                    "display_name" => "namithj",
                    "profile" => "//profiles.wordpress.org/namithj",
                    "avatar" => "https://wordpress.org/grav-redirect.php?user=namithj",
                ],
                "asirota" => [
                    "display_name" => "asirota",
                    "profile" => "//profiles.wordpress.org/asirota",
                    "avatar" => "https://wordpress.org/grav-redirect.php?user=asirota",
                ],
            ],
            "requires" => "5.3",
            "tested" => "6.7",
            "requires_php" => "7.4",
            "requires_plugins" => [
            ],
            "sections" => [
                "description" => "<p>This plugin allows a WordPress user to automatically rewrite certain URLs and URL paths to a new URL. This is helpful because it allows for the rewriting of api.wordpress.org to some other repository that contains the plugins the user wants.</p>
<p>The plugin supports multiple rewrites, and also supports rewriting the URL paths of the requests on a per-host basis. This improves the capacity of the plugin to adequately support newer or different repositories.</p>",
                "changelog" => "<h4> 0.6.1 </h4>
<ul>
<li>Added AspireCloud.io endpoint for bleeding edge testing</li>
<li>Added content type json header for better error retrieval from AC</li>
</ul>
<h4> 0.6 </h4>
<ul>
<li>Admin Settings: Added notices for when settings are saved or reset</li>
<li>Branding: Added branded notices to inform users when AspireUpdate is in operation on a screen</li>
<li>Multisite: Added multisite support</li>
<li>Debug: Added Clear Logs and View Logs functionality</li>
<li>I18N: Added Catalan translation</li>
<li>I18N: Added Dutch translation</li>
<li>I18N: Added Spanish translation</li>
<li>I18N: Added Swedish translation</li>
<li>I18N: Updated Dutch translation</li>
<li>I18N: Updated French translation</li>
<li>I18N: Updated German translation</li>
<li>Testing: Added Git Updater integration</li>
<li>Testing: Added support both main and playground-ready links in the README</li>
<li>Testing: Made Playground default to the <code>main</code> branch</li>
<li>Testing: Removed Hello Dolly from the Playground blueprint</li>
<li>Security: Fixed Plugin Check security warnings</li>
</ul>
<h4> 0.5 </h4>
<ul>
<li>first stable version, connects to api.wordpress.org or an alternative AspireCloud repository</li>
</ul>",
                "faq" => "<h4>A question that someone might have</h4>
<p>An answer to that question.</p>
<h4>What about foo bar?</h4>
<p>Answer to foo bar dilemma.</p>
",
            ],
            "short_description" => "This plugin allows a WordPress user to automatically rewrite certain URLs and URL paths to a new URL. This is helpful because it allows for the rew...",
            "primary_branch" => "main",
            "branch" => "main",
            "download_link" => "https://api.github.com/repos/aspirepress/aspireupdate/zipball/0.6.1",
            "tags" => [],
            "versions" => [
                "0.6.1" => "https://api.github.com/repos/aspirepress/aspireupdate/zipball/0.6.1",
                "0.6" => "https://api.github.com/repos/aspirepress/aspireupdate/zipball/0.6",
                "0.5" => "https://api.github.com/repos/aspirepress/aspireupdate/zipball/0.5",
            ],
            "donate_link" => "https://github.com/sponsors/aspirepress",
            "banners" => [],
            "icons" => [
                "default" => "https://s.w.org/plugins/geopattern-icon/aspireupdate.svg",
            ],
            "last_updated" => "2025-02-21T19:06:33Z",
            "num_ratings" => 0,
            "rating" => 0,
            "active_installs" => 0,
            "homepage" => "https://aspirepress.org/",
            "external" => "xxx",

            'aspiresync_meta' => [
                'type' => 'plugin',
                'slug' => 'aspireupdate',
                'name' => 'AspireUpdate',
                'status' => 'open',
                'version' => '0.6.1',
                'origin' => 'manual',
                // 'updated' => '2015-01-28T21:41:00+00:00',
                // 'pulled' => '2024-11-18T02:13:41+00:00',
            ],
        ];

        Plugin::fromSyncMetadata($metadata)->save();
    }
}
