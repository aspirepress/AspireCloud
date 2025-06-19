<?php

declare(strict_types=1);

use App\Models\WpOrg\ClosedPlugin;
use App\Models\WpOrg\Plugin;
use App\Models\WpOrg\Theme;

describe('Sync Plugins', function () {
    $md_0errors = [
        'slug' => '0-errors',
        'name' => '0-Errors',
        'status' => 'open',
        'version' => '0.2',
        'author' => '<a href="http://zanto.org/">Ayebare Mucunguzi</a>',
        'author_profile' => 'https://profiles.wordpress.org/brooksx/',
        'contributors' => [
            'brooksx' => [
                'profile' => 'https://profiles.wordpress.org/brooksx/',
                'avatar' => 'https://secure.gravatar.com/avatar/4fa021b564189f92bf90322a1215401d?s=96&d=monsterid&r=g',
                'display_name' => 'Ayebare Mucunguzi Brooks',
            ],
        ],
        'requires' => '3.1',
        'tested' => '4.1.41',
        'requires_php' => false,
        'requires_plugins' => [],
        'compatibility' => [],
        'rating' => 100,
        'ratings' => [
            '5' => 1,
            '4' => 0,
            '3' => 0,
            '2' => 0,
            '1' => 0,
        ],
        'num_ratings' => 1,
        'support_url' => 'https://wordpress.org/support/plugin/0-errors/',
        'support_threads' => 0,
        'support_threads_resolved' => 0,
        'active_installs' => 10,
        'downloaded' => 2616,
        'last_updated' => '2015-01-28 9:41pm GMT',
        'added' => '2015-01-20',
        'homepage' => 'http://example.org/',
        'sections' => [
            'description' => '<p>This plugin makes it easy to work with WordPress with-ought the errors messing up the layout as they are nicely hidden in a drop down panel. Also PHP Errors are only shown to the admin and won&#8217;t be visible to the general public. There options to send the admin an email informing him of an error that has occurred on the site. The plugin has options of intercepting Ajax errors and PHP errors generated during Javascript requests and saving them to be viewed for debugging.</p><h3>Features</h3><ul><li>Show PHP errors only to the admin and hide them from the general public</li><li>Prevents PHP errors from breaking the site by displaying them in a drop down panel</li><li>Report PHP site errors to the admin by email</li><li>Capture PHP errors generated during ajax or Javascript requests to be viewed for debugging.</li></ul>',
            'installation' => '<p>Upload the 0-Errors Plugin Base plugin to your blog and activate it. It would work as is.</p>',
            'faq' => '<h4>Is it compatible with latest WordPress?</h4><p><p>Yes, it is, as well as with the latest PHP.</p></p>',
            'changelog' => '<h4>0.2</h4><ul><li>Bug fixes</li></ul><h4>0.1</h4><ul><li>Initial commit</li></ul>',
            'reviews' => '',
        ],
        'short_description' => 'Shows generated php site errors only to the admin via a drop down panel and hides them from the public. Email Alerts the admin of errors.',
        'description' => '<p>This plugin makes it easy to work with WordPress with-ought the errors messing up the layout as they are nicely hidden in a drop down panel. Also PHP Errors are only shown to the admin and won&#8217;t be visible to the general public. There options to send the admin an email informing him of an error that has occurred on the site. The plugin has options of intercepting Ajax errors and PHP errors generated during Javascript requests and saving them to be viewed for debugging.</p><h3>Features</h3><ul><li>Show PHP errors only to the admin and hide them from the general public</li><li>Prevents PHP errors from breaking the site by displaying them in a drop down panel</li><li>Report PHP site errors to the admin by email</li><li>Capture PHP errors generated during ajax or Javascript requests to be viewed for debugging.</li></ul>',
        'download_link' => 'https://downloads.wordpress.org/plugin/0-errors.0.2.zip',
        'upgrade_notice' => [],
        'screenshots' => [],
        'tags' => [
            'debug' => 'debug',
            'email-errors' => 'email errors',
            'errors' => 'errors',
            'error_reporting' => 'error_reporting',
        ],
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

    it('loads metadata', function () use ($md_0errors) {
        $plugin = Plugin::fromSyncMetadata($md_0errors);
        expect($plugin)
            ->toBeInstanceOf(Plugin::class)
            ->and($plugin->slug)->toBe('0-errors')
            ->and($plugin->name)->toBe('0-Errors')
            ->and($plugin->description)->toStartWith(
                "<p>This plugin makes it easy to work with WordPress with-ought the errors messing up the layout",
            )
            ->and($plugin->short_description)->toBe(
                'Shows generated php site errors only to the admin via a drop down panel and hides them from the public. Email Alerts the admin of errors.',
            )
            ->and($plugin->author)->toBe('<a href="http://zanto.org/">Ayebare Mucunguzi</a>')
            ->and($plugin->author_profile)->toBe('https://profiles.wordpress.org/brooksx/')
            ->and($plugin->contributors->toArray()[0])->toMatchArray([
                'user_nicename' => 'brooksx',
                'profile' => 'https://profiles.wordpress.org/brooksx/',
                'avatar' => 'https://secure.gravatar.com/avatar/4fa021b564189f92bf90322a1215401d?s=96&d=monsterid&r=g',
                'display_name' => 'Ayebare Mucunguzi Brooks',
            ])
            ->and($plugin->requires)->toBe('3.1')
            ->and($plugin->tested)->toBe('4.1.41')
            ->and($plugin->requires_php)->toBeNull()
            ->and($plugin->requires_plugins)->toBeEmpty()
            ->and($plugin->compatibility)->toBeEmpty()
            ->and($plugin->rating)->toBe(100)
            ->and($plugin->ratings)->toBe(['5' => 1, '4' => 0, '3' => 0, '2' => 0, '1' => 0])
            ->and($plugin->num_ratings)->toBe(1)
            ->and($plugin->support_url)->toBe('https://wordpress.org/support/plugin/0-errors/')
            ->and($plugin->support_threads)->toBe(0)
            ->and($plugin->support_threads_resolved)->toBe(0)
            ->and($plugin->active_installs)->toBe(10)
            ->and($plugin->downloaded)->toBe(2616)
            ->and($plugin->last_updated)->toBeBetween(
                new DateTime('2015-01-28 9:41pm GMT'),
                new DateTime('2015-01-28 10:41pm GMT'),
            )
            ->and($plugin->added)->toBeBetween(new DateTime('2015-01-20'), new DateTime('2015-01-21'))
            ->and($plugin->homepage)->toBe('http://example.org/')
            ->and($plugin->sections)->toHaveKeys(['description', 'installation', 'faq', 'changelog', 'reviews'])
            ->and($plugin->sections['description'])->toStartWith(
                "<p>This plugin makes it easy to work with WordPress with-ought the errors messing up the layout",
            )
            ->and($plugin->sections['installation'])->toBe(
                "<p>Upload the 0-Errors Plugin Base plugin to your blog and activate it. It would work as is.</p>",
            )
            ->and($plugin->sections['faq'])->toStartWith("<h4>Is it compatible with latest WordPress?</h4>")
            ->and($plugin->sections['changelog'])->toStartWith("<h4>0.2</h4>")
            ->and($plugin->sections['reviews'])->toBeEmpty()
            ->and($plugin->upgrade_notice)->toBeEmpty()
            ->and($plugin->screenshots)->toBeEmpty()
            ->and($plugin->tagsArray())->toBe([
                'debug' => 'debug',
                'email-errors' => 'email errors',
                'errors' => 'errors',
                'error_reporting' => 'error_reporting',
            ])
            ->and($plugin->business_model)->toBeNull()
            ->and($plugin->repository_url)->toBeEmpty()
            ->and($plugin->commercial_support_url)->toBeEmpty()
            ->and($plugin->donate_link)->toBeEmpty()
            ->and($plugin->banners)->toBeEmpty()
            ->and($plugin->preview_link)->toBeEmpty();

        // test URL rewrites
        expect($plugin->download_link)
            ->toBe('https://api.aspiredev.org/download/plugin/0-errors.0.2.zip')
            ->and($plugin->icons)->toBe(
                ['default' => 'https://api.aspiredev.org/download/gp-icon/plugin/0-errors/head/0-errors.svg'],
            )
            ->and($plugin->versions)->toBe([
                '0.1' => 'https://api.aspiredev.org/download/plugin/0-errors.0.1.zip',
                '0.2' => 'https://api.aspiredev.org/download/plugin/0-errors.0.2.zip',
                'trunk' => 'https://api.aspiredev.org/download/plugin/0-errors.zip',
            ]);
    });

    it('throws an exception if loaded as ClosedPlugin', function () use ($md_0errors) {
        expect(function () use ($md_0errors) {
            ClosedPlugin::fromSyncMetadata($md_0errors);
        })->toThrow(InvalidArgumentException::class);
    });

    it('throws an exception if loaded as Theme', function () use ($md_0errors) {
        expect(function () use ($md_0errors) {
            Theme::fromSyncMetadata($md_0errors);
        })->toThrow(InvalidArgumentException::class);
    });
});
