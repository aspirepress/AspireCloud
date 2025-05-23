<?php

declare(strict_types=1);

use App\Models\WpOrg\Theme;

describe('Download URL Rewrites (Plugins)', function () {
    $md_100b = [
        'slug' => '100-bytes',
        'name' => '100 Bytes',
        'status' => 'open',
        'version' => '1.1.3',
        'preview_url' => 'https://wp-themes.com/100-bytes/',
        'author' => [
            'user_nicename' => 'marc4',
            'profile' => 'https://profiles.wordpress.org/marc4/',
            'avatar' => 'https://secure.gravatar.com/avatar/76e5967738212577d98ad75204656d48?s=96&d=monsterid&r=g',
            'display_name' => 'Marc Armengou',
            'author' => 'Marc Armengou',
            'author_url' => 'https://www.marcarmengou.com/',
        ],
        'screenshot_url' => '//ts.w.org/wp-content/themes/100-bytes/screenshot.png?ver=1.1.3',
        'ratings' => [
            '1' => 11,
            '2' => 22,
            '3' => 33,
            '4' => 44,
            '5' => 55,
        ],
        'rating' => 0,
        'num_ratings' => 0,
        'reviews_url' => 'https://wordpress.org/support/theme/100-bytes/reviews/',
        'downloaded' => 1466,
        'active_installs' => 50,
        'last_updated' => '2024-01-13',
        'last_updated_time' => '2024-01-13 15:57:28',
        'creation_time' => '2023-05-31 03:11:40',
        'homepage' => 'https://wordpress.org/themes/100-bytes/',
        'sections' => [
            'description' => '100 Bytes is a theme that aims to look as optimal as possible to deliver your message to your audience using WordPress as a content manager. The idea is simple, make a theme that looks good everywhere, with as little CSS code as possible. In this case the limit is 100 Bytes of CSS information. Actually the compressed CSS code contains 82 bytes of information, but 100 bytes sounds better.',
        ],
        'download_link' => 'https://downloads.wordpress.org/theme/100-bytes.zip',
        'tags' => [
            'blog' => 'Blog',
            'full-width-template' => 'Full width template',
            'one-column' => 'One column',
        ],
        'versions' => [
            '1.0' => 'https://downloads.wordpress.org/theme/100-bytes.1.0.zip',
            '1.0.1' => 'https://downloads.wordpress.org/theme/100-bytes.1.0.1.zip',
            '1.0.2' => 'https://downloads.wordpress.org/theme/100-bytes.1.0.2.zip',
            '1.0.3' => 'https://downloads.wordpress.org/theme/100-bytes.1.0.3.zip',
            '1.0.4' => 'https://downloads.wordpress.org/theme/100-bytes.1.0.4.zip',
            '1.0.5' => 'https://downloads.wordpress.org/theme/100-bytes.1.0.5.zip',
            '1.0.6' => 'https://downloads.wordpress.org/theme/100-bytes.1.0.6.zip',
            '1.0.7' => 'https://downloads.wordpress.org/theme/100-bytes.1.0.7.zip',
            '1.0.8' => 'https://downloads.wordpress.org/theme/100-bytes.1.0.8.zip',
            '1.0.9' => 'https://downloads.wordpress.org/theme/100-bytes.1.0.9.zip',
            '1.1.0' => 'https://downloads.wordpress.org/theme/100-bytes.1.1.0.zip',
            '1.1.1' => 'https://downloads.wordpress.org/theme/100-bytes.1.1.1.zip',
            '1.1.2' => 'https://downloads.wordpress.org/theme/100-bytes.1.1.2.zip',
            '1.1.3' => 'https://downloads.wordpress.org/theme/100-bytes.1.1.3.zip',
        ],
        'requires' => false,
        'requires_php' => '5.6',
        'is_commercial' => false,
        'external_support_url' => false,
        'is_community' => false,
        'external_repository_url' => '',
        'aspiresync_meta' => [
            'id' => '01933d4b-3cd8-70b2-831d-9e8398ded9b4',
            'type' => 'theme',
            'slug' => '100-bytes',
            'name' => '100 Bytes',
            'status' => 'open',
            'version' => '1.1.3',
            'origin' => 'wp_org',
            'updated' => '2024-01-13T00:00:00+00:00',
            'pulled' => '2024-11-18T03:22:41+00:00',
        ],
    ];

    it('uses link from version if no version in download_link', function () use ($md_100b) {
        $theme = Theme::fromSyncMetadata($md_100b);
        expect($theme->download_link)->toBe('https://api.aspiredev.org/download/theme/100-bytes.1.1.3.zip');
    });

    it('returns original url if no version found', function () use ($md_100b) {
        $metadata = $md_100b;
        unset($metadata['versions'][$metadata['version']]);
        $plugin = Theme::fromSyncMetadata($metadata);
        expect($plugin->download_link)->toBe('https://downloads.wordpress.org/theme/100-bytes.zip');
    });
});
