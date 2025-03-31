<?php

use App\Models\WpOrg\Author;
use App\Models\WpOrg\Theme;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $author = Author::create([
        'user_nicename' => 'tmeister',
        'profile' => 'https://profiles.wp.org/tmeister',
        'avatar' => 'https://avatars.wp.org/tmeister',
        'display_name' => 'Tmeister',
        'author' => 'Tmeister',
        'author_url' => 'https://wp-themes.com/author/tmeister',
    ]);

    Theme::create([
        'slug' => 'my-theme',
        'name' => 'My Theme',
        'description' => 'My Theme',
        'version' => '1.2.1',
        'download_link' => 'https://downloads.wp/my-theme',
        'requires_php' => '5.6',
        // 'last_updated' => CarbonImmutable::parse('2022-02-22 22:22:22'),
        'last_updated' => '2025-01-18T20:50:36+00:00',
        'creation_time' => CarbonImmutable::parse('2011-11-11 11:11:11'),
        'preview_url' => 'https://wp-themes.com/my-theme',
        'screenshot_url' => 'https://wp-themes.com/my-theme/screenshot.png',
        'rating' => 5,
        'num_ratings' => 6,
        'reviews_url' => 'https://wp-themes.com/my-theme/reviews',
        'downloaded' => 1000,
        'active_installs' => 100,
        'homepage' => 'https://wp-themes.com/my-theme',
        'is_commercial' => false,
        'external_support_url' => null,
        'is_community' => true,
        'external_repository_url' => 'https://test.com',
        'author_id' => $author->id,
        'ac_origin' => 'wp_org',
        'ac_raw_metadata' => [],
    ])->addTagsBySlugs(['black', 'white', 'red', 'blue']);
    // 'ratings' => [5, 4, 3, 2, 1, 2],
    // 'requires' => ['php' => '5.6', 'wp' => '5.0'],
    // 'versions' => ['1.2.1', '1.2.0', '1.1.0'],
});

it('returns 400 when slug is missing', function () {
    $response = $this->get('/themes/info/1.2?action=theme_information');

    $response
        ->assertStatus(400)
        ->assertJson(['error' => 'The slug field is required.']);
});

it('returns 404 when theme does not exist', function () {
    $response = $this->get('/themes/info/1.2?action=theme_information&slug=non-existent-theme');

    $response
        ->assertStatus(404)
        ->assertJson(['error' => 'Theme not found']);
});

it('returns theme_information in wp.org format (v1.1)', function () {
    $response = $this->get('/themes/info/1.1?action=theme_information&slug=my-theme');

    $response
        ->assertStatus(200)
        ->assertExactJson([
            'author' => 'tmeister',
            'download_link' => 'https://api.aspiredev.org/download/my-theme',
            'downloaded' => 1000,
            'homepage' => 'https://wordpress.org/themes/my-theme/',
            'last_updated' => '2025-01-18',
            'last_updated_time' => '2025-01-18 20:50:36',
            'name' => 'My Theme',
            'num_ratings' => 6,
            'preview_url' => 'https://wp-themes.com/my-theme',
            'rating' => 5,
            'screenshot_url' => 'https://wp-themes.com/my-theme/screenshot.png',
            'sections' => [],
            'slug' => 'my-theme',
            'tags' => [
                'black' => 'black',
                'blue' => 'blue',
                'red' => 'red',
                'white' => 'white',
            ],
            'version' => '1.2.1',
        ]);
});

it('returns theme_information in wp.org format (v1.2)', function () {
    $response = $this->get('/themes/info/1.2?action=theme_information&slug=my-theme');

    $response
        ->assertStatus(200)
        ->assertExactJson([
            'author' => [
                'author' => 'Tmeister',
                'author_url' => 'https://wp-themes.com/author/tmeister',
                'avatar' => 'https://avatars.wp.org/tmeister',
                'display_name' => 'Tmeister',
                'profile' => 'https://profiles.wp.org/tmeister',
                'user_nicename' => 'tmeister',
            ],
            'creation_time' => '2011-11-11 11:11:11',
            'download_link' => 'https://api.aspiredev.org/download/my-theme',
            'downloaded' => 1000,
            'external_repository_url' => 'https://test.com',
            'external_support_url' => false,
            'homepage' => 'https://wordpress.org/themes/my-theme/',
            'is_commercial' => false,
            'is_community' => true,
            'last_updated' => '2025-01-18',
            'last_updated_time' => '2025-01-18 20:50:36',
            'name' => 'My Theme',
            'num_ratings' => 6,
            'preview_url' => 'https://wp-themes.com/my-theme',
            'rating' => 5,
            'requires' => null,
            'requires_php' => '5.6',
            'reviews_url' => 'https://wp-themes.com/my-theme/reviews',
            'screenshot_url' => 'https://wp-themes.com/my-theme/screenshot.png',
            'sections' => [],
            'slug' => 'my-theme',
            'tags' => [
                'black' => 'black',
                'blue' => 'blue',
                'red' => 'red',
                'white' => 'white',
            ],
            'version' => '1.2.1',
        ]);
});

it('returns theme query results in wp.org format (v1.1)', function () {
    $this
        ->get('/themes/info/1.1?action=query_themes')
        ->assertStatus(200)
        ->assertExactJson([
            'info' => ['page' => 1, 'pages' => 1, 'results' => 1],
            'themes' => [
                [
                    'author' => 'tmeister',
                    'description' => 'My Theme',
                    'homepage' => 'https://wordpress.org/themes/my-theme/',
                    'name' => 'My Theme',
                    'num_ratings' => 6,
                    'preview_url' => 'https://wp-themes.com/my-theme',
                    'rating' => 5,
                    'screenshot_url' => 'https://wp-themes.com/my-theme/screenshot.png',
                    'slug' => 'my-theme',
                    'version' => '1.2.1',
                ],
            ],
        ]);
});

it('returns theme query results in wp.org format (v1.2)', function () {
    $this
        ->get('/themes/info/1.2?action=query_themes')
        ->assertStatus(200)
        ->assertExactJson([
            'info' => ['page' => 1, 'pages' => 1, 'results' => 1],
            'themes' => [
                [
                    'author' => [
                        'author' => 'Tmeister',
                        'author_url' => 'https://wp-themes.com/author/tmeister',
                        'avatar' => 'https://avatars.wp.org/tmeister',
                        'display_name' => 'Tmeister',
                        'profile' => 'https://profiles.wp.org/tmeister',
                        'user_nicename' => 'tmeister',
                    ],
                    'description' => 'My Theme',
                    'external_repository_url' => 'https://test.com',
                    'external_support_url' => false,
                    'homepage' => 'https://wordpress.org/themes/my-theme/',
                    'is_commercial' => false,
                    'is_community' => true,
                    'name' => 'My Theme',
                    'num_ratings' => 6,
                    'preview_url' => 'https://wp-themes.com/my-theme',
                    'rating' => 5,
                    'requires' => null,
                    'requires_php' => '5.6',
                    'screenshot_url' => 'https://wp-themes.com/my-theme/screenshot.png',
                    'slug' => 'my-theme',
                    'version' => '1.2.1',
                ],
            ],
        ]);

});

it('returns hot tags results in wp.org format (v1.1)', function () {
    $this
        ->get('/themes/info/1.1?action=hot_tags')
        ->assertStatus(200)
        ->assertExactJson([
            'black' => ['count' => 1, 'name' => 'black', 'slug' => 'black'],
            'blue' => ['count' => 1, 'name' => 'blue', 'slug' => 'blue'],
            'red' => ['count' => 1, 'name' => 'red', 'slug' => 'red'],
            'white' => ['count' => 1, 'name' => 'white', 'slug' => 'white'],
        ]);
    // TODO: test the actual hot tags sorting algorithm with multiple plugins
});
