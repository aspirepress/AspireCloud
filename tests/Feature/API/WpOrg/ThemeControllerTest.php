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

it('returns theme_information (v1.1)', function () {
    $response = $this->get('/themes/info/1.1?action=theme_information&slug=my-theme');

    $response
        ->assertStatus(200)
        ->assertJson([
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

it('returns all fields in theme_information (v1.2)', function () {
    $response = $this->get('/themes/info/1.2?action=theme_information&slug=my-theme');

    $response
        ->assertStatus(200)
        ->assertJson([
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
            'homepage' => 'https://wordpress.org/themes/my-theme/',
            'is_commercial' => false,
            'is_community' => true,
            'last_updated' => '2025-01-18',
            'last_updated_time' => '2025-01-18 20:50:36',
            'name' => 'My Theme',
            'num_ratings' => 6,
            'preview_url' => 'https://wp-themes.com/my-theme',
            'rating' => 5,
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

it('returns theme query results (v1.1)', function () {
    $this
        ->get('/themes/info/1.1?action=query_themes')
        ->assertStatus(200)
        ->assertJson([
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

it('returns theme query results (v1.2)', function () {
    $this
        ->get('/themes/info/1.2?action=query_themes')
        ->assertStatus(200)
        ->assertJson([
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
                    'homepage' => 'https://wordpress.org/themes/my-theme/',
                    'is_commercial' => false,
                    'is_community' => true,
                    'name' => 'My Theme',
                    'num_ratings' => 6,
                    'preview_url' => 'https://wp-themes.com/my-theme',
                    'rating' => 5,
                    'requires_php' => '5.6',
                    'screenshot_url' => 'https://wp-themes.com/my-theme/screenshot.png',
                    'slug' => 'my-theme',
                    'version' => '1.2.1',
                ],
            ],
        ])
        // GH-278: return all fields.  these are not normally returned by default by .org
        ->assertJsonPath('themes.0.download_link', 'https://api.aspiredev.org/download/my-theme')
        ->assertJsonPath('themes.0.downloaded', 1000)
        ->assertJsonPath('themes.0.active_installs', 100)
        ->assertJsonPath('themes.0.tags.black', 'black');
});

it('returns theme query results for tags (v1.2)', function () {
    $this
        ->get('/themes/info/1.2?action=query_themes&tag[]=black&tag[]=orange')
        ->assertStatus(200)
        ->assertJson([
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
                    'homepage' => 'https://wordpress.org/themes/my-theme/',
                    'is_commercial' => false,
                    'is_community' => true,
                    'name' => 'My Theme',
                    'num_ratings' => 6,
                    'preview_url' => 'https://wp-themes.com/my-theme',
                    'rating' => 5,
                    'requires_php' => '5.6',
                    'screenshot_url' => 'https://wp-themes.com/my-theme/screenshot.png',
                    'slug' => 'my-theme',
                    'version' => '1.2.1',
                ],
            ],
        ]);

    $this
        ->get('/themes/info/1.2?action=query_themes&tag=orange')
        ->assertStatus(200)
        ->assertExactJson([
            'info' => ['page' => 1, 'pages' => 0, 'results' => 0],  // page 1 of 0 is a bit odd but it is correct
            'themes' => [],
        ]);
});

it('returns theme query results for ac_tags (v1.2)', function () {
    $this
        ->get('/themes/info/1.2?action=query_themes&ac_tag[]=black&ac_tag[]=blue')
        ->assertStatus(200)
        ->assertJson([
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
                    'homepage' => 'https://wordpress.org/themes/my-theme/',
                    'is_commercial' => false,
                    'is_community' => true,
                    'name' => 'My Theme',
                    'num_ratings' => 6,
                    'preview_url' => 'https://wp-themes.com/my-theme',
                    'rating' => 5,
                    'requires_php' => '5.6',
                    'screenshot_url' => 'https://wp-themes.com/my-theme/screenshot.png',
                    'slug' => 'my-theme',
                    'version' => '1.2.1',
                ],
            ],
        ]);

    $this
        ->get('/themes/info/1.2?action=query_themes&tag=orange')
        ->assertStatus(200)
        ->assertExactJson([
            'info' => ['page' => 1, 'pages' => 0, 'results' => 0],  // page 1 of 0 is a bit odd but it is correct
            'themes' => [],
        ]);
});

it('ANDs together ac_tags (v1.2)', function () {
    $this
        ->get('/themes/info/1.2?action=query_themes&ac_tag[]=black&ac_tag[]=orange')
        ->assertStatus(200)
        ->assertJson([
            'info' => ['page' => 1, 'pages' => 0, 'results' => 0],
            'themes' => [],
        ]);

    $this
        ->get('/themes/info/1.2?action=query_themes&tag=orange')
        ->assertStatus(200)
        ->assertExactJson([
            'info' => ['page' => 1, 'pages' => 0, 'results' => 0],  // page 1 of 0 is a bit odd but it is correct
            'themes' => [],
        ]);
});

it('returns hot tags results (v1.1)', function () {
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

it('returns latest feature list when no wp version given (v1.2)', function () {
    $this
        ->get('/themes/info/1.2?action=feature_list')
        ->assertStatus(200)
        ->assertExactJsonStructure(['Features', 'Layout', 'Subject'])
        ->assertJsonCount(26, 'Features')
        ->assertJsonCount(8, 'Layout')
        ->assertJsonCount(9, 'Subject');
});

it('returns feature list for wp version < 3.7.999 (v1.2)', function () {
    $this
        ->get('/themes/info/1.2?action=feature_list&wp_version=3.7.998')
        ->assertStatus(200)
        ->assertExactJsonStructure(['Colors', 'Columns', 'Features', 'Subject', 'Width'])
        ->assertJsonCount(15, 'Colors')
        ->assertJsonCount(6, 'Columns')
        ->assertJsonCount(19, 'Features')
        ->assertJsonCount(3, 'Subject')
        ->assertJsonCount(2, 'Width');
});

it('returns latest feature list when no user-agent set (v1.1)', function () {
    $this
        ->get('/themes/info/1.1?action=feature_list')
        ->assertStatus(200)
        ->assertExactJsonStructure(['Features', 'Layout', 'Subject'])
        ->assertJsonCount(26, 'Features')
        ->assertJsonCount(8, 'Layout')
        ->assertJsonCount(9, 'Subject');
});

// perverse, but something should test it
it('returns latest feature list in serialized object format (v1.0)', function () {
    $body = $this
        ->get('/themes/info/1.0?action=feature_list')
        ->assertStatus(200)
        ->content();

    $response = unserialize($body);
    expect($response)->toBeObject();
    expect($response)->toHaveProperty('Features');
    expect($response)->toHaveProperty('Layout');
    expect($response)->toHaveProperty('Subject');
    expect($response->Features)->toBeArray()->toHaveCount(26);
    expect($response->Layout)->toBeArray()->toHaveCount(8);
    expect($response->Subject)->toBeArray()->toHaveCount(9);
});

it('returns feature list for wp version < 3.7.999 (v1.1)', function () {
    $this
        ->withHeader('User-Agent', 'WordPress/3.7.998')
        ->get('/themes/info/1.1?action=feature_list')
        ->assertStatus(200)
        ->assertExactJsonStructure(['Colors', 'Columns', 'Features', 'Subject', 'Width'])
        ->assertJsonCount(15, 'Colors')
        ->assertJsonCount(6, 'Columns')
        ->assertJsonCount(19, 'Features')
        ->assertJsonCount(3, 'Subject')
        ->assertJsonCount(2, 'Width');
});

it('rejects invalid action with 404 not found', function () {
    $response = $this->get('/themes/info/1.2?action=bogus')->assertNotFound();
});
