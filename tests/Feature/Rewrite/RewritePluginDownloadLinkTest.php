<?php

declare(strict_types=1);

use App\Models\WpOrg\Plugin;

describe('Download URL Rewrites (Plugins)', function () {
    it('uses link from version if no version in download_link', function () {
        $metadata = [
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
        $plugin = Plugin::fromSyncMetadata($metadata);
        expect($plugin->download_link)->toBe('https://api.aspiredev.org/download/plugin/0-errors.0.2.zip');
    });

    it('returns original url if no version found', function () {
        // real-world example from .org
        $metadata = [
            "name" => "Universal Social Reputation",
            "slug" => "acmesocial",
            "version" => "1.0",
            "author" => "<a href=\"https://profiles.wordpress.org/olorapino/\">olorapino</a>",
            "author_profile" => "https://profiles.wordpress.org/olorapino/",
            "contributors" => [
                "olorapino" => [
                    "profile" => "https://profiles.wordpress.org/olorapino/",
                    "avatar" => "https://secure.gravatar.com/avatar/57a882c8f62c4f9b13bd7edfb58dd2b225e1137879c8a978ccbce98557671333?s=96&d=monsterid&r=g",
                    "display_name" => "olorapino",
                ],
            ],
            "requires" => "4.6",
            "tested" => "4.7.29",
            "requires_php" => false,
            "requires_plugins" => [
            ],
            "rating" => 0,
            "ratings" => [0, 0, 0, 0, 0],
            "num_ratings" => 0,
            "support_url" => "https://wordpress.org/support/plugin/acmesocial/",
            "support_threads" => 0,
            "support_threads_resolved" => 0,
            "active_installs" => 0,
            "last_updated" => "2017-02-04 1:23am GMT",
            "added" => "2017-02-02",
            "homepage" => "http://unisocrep.herokuapp.com/",
            "download_link" => "https://downloads.wordpress.org/plugin/acmesocial.zip", // no version in filename
            "upgrade_notice" => [],
            "screenshots" => [],
            "tags" => [
                "comments" => "comments",
                "score" => "score",
                "social" => "social",
                "spam" => "spam",
                "usr" => "USR",
            ],
            "versions" => [], // This is the kicker, no versions exist
            "business_model" => false,
            "repository_url" => "",
            "commercial_support_url" => "",
            "donate_link" => "",
            "banners" => [
                "low" => "https://ps.w.org/acmesocial/assets/banner-772x250.png?rev=1593412",
                "high" => "https://ps.w.org/acmesocial/assets/banner-1544x500.png?rev=1593412",
            ],
            "preview_link" => "",
            'aspiresync_meta' => [
                'type' => 'plugin',
                'slug' => '0-errors',
                'name' => '0-Errors',
                'status' => 'open',
                'version' => '0.2',
                'origin' => 'wp_org',
                'updated' => '2025-01-28T21:41:00+00:00',
                'pulled' => '2025-11-18T02:13:41+00:00',
            ],
        ];

        $plugin = Plugin::fromSyncMetadata($metadata);
        expect($plugin->download_link)->toBe('https://downloads.wordpress.org/plugin/acmesocial.zip');
    });
});
