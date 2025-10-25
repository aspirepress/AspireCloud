<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\WpOrg\Plugin;
use App\Services\Repo\BarePluginRepo;
use Illuminate\Database\Seeder;

class PluginSeeder extends Seeder
{
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
            "version" => "1.0",
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
            "tested" => "6.8.1",
            "requires_php" => "7.4",
            "requires_plugins" => [
            ],
            "sections" => [
                'description' => '<p>This plugin allows a WordPress user to automatically rewrite certain URLs and URL paths to a new URL. This is helpful because it allows for the rewriting of api.wordpress.org to some other repository that contains the plugins the user wants.</p>\n<p>The plugin supports multiple rewrites, and also supports rewriting the URL paths of the requests on a per-host basis. This improves the capacity of the plugin to adequately support newer or different repositories.</p>',
                'changelog' => '<p>[unreleased]</p>\n<h4>1.0</h4>\n<ul>\n<li>Removed the Aspire Cloud Bleeding Edge Endpoint from Hosts List.</li>\n</ul>\n<h4>0.9.4</h4>\n<ul>\n<li>Admin Settings: WordPress.org has been added as an API host option, and is the new default value.</li>\n<li>Admin Settings: A new admin bar menu has been added to display the current API host.</li>\n<li>Admin Settings: Admin notices now replace browser alerts when managing the log.</li>\n<li>Admin Settings: The &quot;Clear Log&quot; and &quot;View Log&quot; elements are now only visible when a log is known to exist.</li>\n<li>API Rewrite: A new AP_BYPASS_CACHE constant may be used to add cache busting to API requests.</li>\n<li>Branding: The branding notice is now permanently dismissible.</li>\n<li>Accessibility: The link in the branding notice now has more descriptive text.</li>\n<li>Accessibility: The &quot;Generate API Key&quot; button now has a visual label.</li>\n<li>Accessibility: The Voltron easter egg now uses a role of &quot;img&quot; with a label.</li>\n<li>Accessibility: The Voltron easter egg\'s animation now respects user motion preferences.</li>\n<li>Accessibility: Field labels and descriptions, and their associations, have been improved.</li>\n<li>Accessibility: The &quot;View Log&quot; popup has been removed, and the &quot;View Log&quot; button is now a link to the log file.</li>\n<li>Package: The &quot;Tested up to&quot; plugin header has now been set to WordPress 6.8.1.</li>\n<li>Package: Hosts data is now stored in a new hosts.json file in the plugin\'s root directory.</li>\n<li>Workflows: PHPUnit tests will now run against PHP 8.4.</li>\n<li>Workflows: End-to-end (E2E) tests now only run when manually triggered.</li>\n</ul>\n<h4>0.9.3</h4>\n<ul>\n<li>Compatibility: API rewrites now occur on a late hook priority.</li>\n<li>Compatibility: API rewriting can be optionally skipped if the request already has a response.</li>\n<li>Documentation: CHANGES.md is now used for the changelog instead of readme.txt.</li>\n<li>Documentation: The default AP_HOST value in README.md is now api.aspirecloud.net.</li>\n<li>Package: The dash in &quot;aspire-update&quot; has been removed from the package name.</li>\n<li>Dependencies: The translations-updater dependency has been updated to 1.2.1.</li>\n</ul>\n<h4>0.9.2</h4>\n<ul>\n<li>Package: The plugin\'s version has been updated.</li>\n</ul>\n<h4>0.9.1</h4>\n<ul>\n<li>First 0.9.x release because 0.9 was not properly versioned and tagged.</li>\n</ul>\n<h4>0.9 (never released)</h4>\n<ul>\n<li>New downloadable release for &quot;Beta Soft Launch&quot; - no changes from 0.6.2.</li>\n</ul>\n<h4>0.6.2</h4>\n<p>TODO: WRITEME</p>\n<h4>0.6.1</h4>\n<ul>\n<li>Added AspireCloud.io endpoint for bleeding edge testing</li>\n<li>Added content type json header for better error retrieval from AC</li>\n</ul>\n<h4>0.6</h4>\n<ul>\n<li>Admin Settings: Added notices for when settings are saved or reset</li>\n<li>Branding: Added branded notices to inform users when AspireUpdate is in operation on a screen</li>\n<li>Multisite: Added multisite support</li>\n<li>Debug: Added Clear Logs and View Logs functionality</li>\n<li>I18N: Added Catalan translation</li>\n<li>I18N: Added Dutch translation</li>\n<li>I18N: Added Spanish translation</li>\n<li>I18N: Added Swedish translation</li>\n<li>I18N: Updated Dutch translation</li>\n<li>I18N: Updated French translation</li>\n<li>I18N: Updated German translation</li>\n<li>Testing: Added Git Updater integration</li>\n<li>Testing: Added support both main and playground-ready links in the README</li>\n<li>Testing: Made Playground default to the <code>main</code> branch</li>\n<li>Testing: Removed Hello Dolly from the Playground blueprint</li>\n<li>Security: Fixed Plugin Check security warnings</li>\n</ul>\n<h4>0.5</h4>\n<ul>\n<li>first stable version, connects to api.wordpress.org or an alternative AspireCloud repository</li>\n</ul>',
                'faq' => '<h4>A question that someone might have</h4>\n<p>An answer to that question.</p>\n<h4>What about foo bar?</h4>\n<p>Answer to foo bar dilemma.</p>\n',
            ],
            "short_description" => "This plugin allows a WordPress user to automatically rewrite certain URLs and URL paths to a new URL. This is helpful because it allows for the rew...",
            "primary_branch" => "main",
            "branch" => "main",
            "download_link" => "https://github.com/aspirepress/aspireupdate/releases/download/1.0/aspireupdate.zip",
            "tags" => [],
            "versions" => [
                "1.0" => "https://github.com/aspirepress/aspireupdate/releases/download/1.0/aspireupdate.zip",
                "0.6.1" => "https://api.github.com/repos/aspirepress/aspireupdate/zipball/0.6.1",
                "0.6" => "https://api.github.com/repos/aspirepress/aspireupdate/zipball/0.6",
                "0.5" => "https://api.github.com/repos/aspirepress/aspireupdate/zipball/0.5",
            ],
            "donate_link" => "",
            "banners" => [],
            "icons" => [
                "default" => "https://s.w.org/plugins/geopattern-icon/aspireupdate.svg",
            ],
            "last_updated" => "2025-06-04T19:06:33Z",
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
                'version' => '1.0',
                'origin' => 'manual',
                // 'updated' => '2015-01-28T21:41:00+00:00',
                // 'pulled' => '2024-11-18T02:13:41+00:00',
            ],
        ];

        Plugin::fromSyncMetadata($metadata)->save();
    }
}
