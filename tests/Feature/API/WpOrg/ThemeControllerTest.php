<?php

use App\Models\WpOrg\Author;
use App\Models\WpOrg\Theme;
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
        'last_updated' => '2021-01-11 12:00:00',
        'creation_time' => '2021-01-01 12:00:00',
        'preview_url' => 'https://wp-themes.com/my-theme',
        'screenshot_url' => 'https://wp-themes.com/my-theme/screenshot.png',
        'ratings' => [5, 4, 3, 2, 1, 2],
        'rating' => 5,
        'num_ratings' => 6,
        'reviews_url' => 'https://wp-themes.com/my-theme/reviews',
        'downloaded' => 1000,
        'active_installs' => 100,
        'homepage' => 'https://wp-themes.com/my-theme',
        'sections' => [],
        'tags' => ['black', 'white', 'red', 'blue'],
        'versions' => ['1.2.1', '1.2.0', '1.1.0'],
        'requires' => ['php' => '5.6', 'wp' => '5.0'],
        'is_commercial' => false,
        'external_support_url' => null,
        'is_community' => true,
        'external_repository_url' => 'https://test.com',
        'author_id' => $author->id,
    ]);

    // Theme::factory()->count(8)->create();
});

it('returns 400 when slug is missing', function () {
    $response = makeApiRequest('GET', '/themes/info/1.2?action=theme_information');

    $response->assertStatus(400)
        ->assertJson([
            'error' => 'The slug field is required.',
        ]);
});

it('returns 404 when theme does not exist', function () {
    $response = makeApiRequest('GET', '/themes/info/1.2?action=theme_information&slug=non-existent-theme');

    $response->assertStatus(404)
        ->assertJson([
            'error' => 'Theme not found',
        ]);
});

it('returns theme_information in wp.org format (v1.1)', function () {
    $response = makeApiRequest('GET', '/themes/info/1.1?action=theme_information&slug=my-theme');

    $response->assertStatus(200)
        ->assertJson([
            'name' => 'My Theme',
        ]);

    assertWpThemeAPIStructure1_1_theme_information($response);
});

it('returns theme_information in wp.org format (v1.2)', function () {
    $response = makeApiRequest('GET', '/themes/info/1.2?action=theme_information&slug=my-theme');

    $response->assertStatus(200)
        ->assertJson([
            'name' => 'My Theme',
        ]);

    assertWpThemeAPIStructure1_2_theme_information($response);
});

it('returns theme query results in wp.org format (v1.1)', function () {
    $response = makeApiRequest('GET', '/themes/info/1.1?action=query_themes');

    $response->assertStatus(200)
        ->assertJson([
            'info' => [
                'page' => 1,
                'pages' => 1,
                'results' => 1,
            ],
        ]);

    assertWpThemeAPIStructure1_1_query_themes($response);
});

it('returns theme query results in wp.org format (v1.2)', function () {
    $response = makeApiRequest('GET', '/themes/info/1.2?action=query_themes');

    $response->assertStatus(200)
        ->assertJson([
            'info' => [
                'page' => 1,
                'pages' => 1,
                'results' => 1,
            ],
        ]);

    assertWpThemeAPIStructure1_2_query_themes($response);
});
