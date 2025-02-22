<?php

declare(strict_types=1);

use App\Models\WpOrg\ClosedPlugin;
use App\Models\WpOrg\Plugin;
use App\Models\WpOrg\Theme;

describe('Download URL Rewrites (Plugins)', function () {
    $no_version_in_download_link = [
        'slug' => '0-errors',
        'name' => '0-Errors',
        'status' => 'open',
        'version' => '0.2',
        'author' => '<a href="http://zanto.org/">Ayebare Mucunguzi</a>',
        'author_profile' => 'https://profiles.wordpress.org/brooksx/',
        'requires' => '3.1',
        'tested' => '4.1.41',
        'requires_php' => false,
        'requires_plugins' => [],
        'compatibility' => [],
        'rating' => 100,
        'num_ratings' => 1,
        'support_url' => 'https://wordpress.org/support/plugin/0-errors/',
        'support_threads' => 0,
        'support_threads_resolved' => 0,
        'active_installs' => 10,
        'downloaded' => 2616,
        'last_updated' => '2015-01-28 9:41pm GMT',
        'added' => '2015-01-20',
        'homepage' => 'http://example.org/',
        'short_description' => '',
        'description' => '',
        'download_link' => 'https://downloads.wordpress.org/plugin/0-errors.zip',
        'upgrade_notice' => [],
        'screenshots' => [],
        'versions' => [
            '0.1' => 'https://downloads.wordpress.org/plugin/0-errors.0.1.zip',
            '0.2' => 'https://downloads.wordpress.org/plugin/0-errors.0.2.zip',
            'trunk' => 'https://downloads.wordpress.org/plugin/0-errors.zip',
        ],
        'business_model' => false,
        'repository_url' => '',
        'commercial_support_url' => '',
        'donate_link' => '',
        'banners' => [],
        'icons' => [
            'default' => 'https://s.w.org/plugins/geopattern-icon/0-errors.svg',
        ],
        'author_block_count' => 0,
        'author_block_rating' => 100,
        'preview_link' => '',
        'aspiresync_meta' => [
            'id' => '01933d0c-12a5-71f0-95eb-f6a036e963bb',
            'type' => 'plugin',
            'slug' => '0-errors',
            'name' => '0-Errors',
            'status' => 'open',
            'version' => '0.2',
            'origin' => 'wp_org',
            'updated' => '2015-01-28T21:41:00+00:00',
            'pulled' => '2024-11-18T02:13:41+00:00',
        ],
    ];

    it('uses link from version if no version in download_link', function () use ($no_version_in_download_link) {
        $plugin = Plugin::fromSyncMetadata($no_version_in_download_link);
        expect($plugin->download_link)->toBe('https://api.aspiredev.org/download/plugin/0-errors.0.2.zip');
    });

    it('returns original url if no version found', function () use ($no_version_in_download_link) {
        $metadata = $no_version_in_download_link;
        unset($metadata['versions'][$metadata['version']]);
        $plugin = Plugin::fromSyncMetadata($metadata);
        expect($plugin->download_link)->toBe('https://downloads.wordpress.org/plugin/0-errors.zip');
    });
});
