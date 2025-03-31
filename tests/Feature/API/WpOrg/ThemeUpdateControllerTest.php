<?php

use App\Models\WpOrg\Theme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use function Safe\json_encode;

uses(RefreshDatabase::class);

beforeEach(function () {
    $authorId = Str::uuid();
    DB::table('authors')->insert([
        'id' => $authorId,
        'user_nicename' => 'author-name',
        'display_name' => 'Author Name',
        'author' => 'author@example.com',
    ]);
    Theme::create([
        'slug' => 'my-theme',
        'name' => 'My Theme',
        'description' => 'Description of My Theme',
        'version' => '1.2.1',
        'download_link' => 'https://downloads.wp/my-theme',
        'requires_php' => '5.6',
        'last_updated' => '2021-01-11 12:00:00',
        'creation_time' => '2021-01-01 12:00:00',
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
        'author_id' => $authorId->toString(),
        'ac_origin' => 'wp_org',
        'ac_raw_metadata' => [],
    ])->addTagsBySlugs(['black', 'white', 'red', 'blue']);
    // 'ratings' => [5, 4, 3, 2, 1, 2],
    // 'requires' => ['php' => '5.6', 'wp' => '5.0'],
    // 'versions' => ['1.2.1', '1.2.0', '1.1.0'],

    Theme::create([
        'slug' => 'my-theme2',
        'name' => 'My Theme2',
        'version' => '2.9',
        'description' => 'Description of My Theme2',
        'download_link' => 'https://downloads.wp/my-theme2',
        'requires_php' => '5.6',
        'last_updated' => '2021-01-11 12:00:00',
        'creation_time' => '2021-01-01 12:00:00',
        'preview_url' => 'https://wp-themes.com/my-theme2',
        'screenshot_url' => 'https://wp-themes.com/my-theme2/screenshot.png',
        'rating' => 5,
        'num_ratings' => 6,
        'reviews_url' => 'https://wp-themes.com/my-theme2/reviews',
        'downloaded' => 1000,
        'active_installs' => 100,
        'homepage' => 'https://wp-themes.com/my-theme2',
        'is_commercial' => false,
        'external_support_url' => null,
        'is_community' => true,
        'external_repository_url' => 'https://test.com',
        'author_id' => $authorId->toString(),
        'ac_origin' => 'wp_org',
        'ac_raw_metadata' => [],
    ])->addTagsBySlugs(['black', 'white', 'red', 'blue']);
    // 'ratings' => [5, 4, 3, 2, 1, 2],
    // 'requires' => ['php' => '5.6', 'wp' => '5.0'],
    // 'versions' => ['1.2.1', '1.2.0', '1.1.0'],
});

it('returns theme updates', function () {
    $response = $this->post('/themes/update-check/1.1', [
        'themes' => json_encode([
            "active" => "my-theme",
            "themes" => [
                "my-theme" => [
                    "Name" => "my-theme",
                    "Title" => "My Theme",
                    "Version" => "1.2.0",
                    "Author" => "Author",
                    "Author URI" => "http://www.author.com",
                    "UpdateURI" => "",
                    "Template" => "my-theme",
                    "Stylesheet" => "my-theme",
                ],
                "my-theme2" => [
                    "Name" => "my-theme2",
                    "Title" => "My Theme 2",
                    "Version" => "3.0",
                    "Author" => "Author",
                    "Author URI" => "http://www.author.com",
                    "UpdateURI" => "",
                    "Template" => "my-theme",
                    "Stylesheet" => "my-theme",
                ],
            ],
        ]),
        'translations' => "[]",
        'locale' => "[\"en_US\"]",
    ], [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(200);
    $response
        ->assertJsonCount(1, 'themes')
        ->assertJsonCount(1, 'no_update')
        ->assertJson([
            'themes' => [
                'my-theme' => [
                    'name' => 'My Theme',
                    'theme' => 'my-theme',
                    'new_version' => '1.2.1',
                    'url' => 'https://api.aspiredev.org/download/my-theme',
                    'package' => 'https://api.aspiredev.org/download/my-theme',
                    'requires' => null,
                    'requires_php' => '5.6',
                ],
            ],
            'no_update' => [
                'my-theme2' => [
                    'name' => 'My Theme2',
                    'theme' => 'my-theme2',
                    'new_version' => '2.9',
                    'url' => 'https://api.aspiredev.org/download/my-theme2',
                    'package' => 'https://api.aspiredev.org/download/my-theme2',
                    'requires' => null,
                    'requires_php' => '5.6',
                ],
            ],
            'translations' => [],
        ]);
});

it('returns theme updates - no_updates', function () {
    $response = $this->post('/themes/update-check/1.1', [
        'themes' => json_encode([
            "active" => "my-theme",
            "themes" => [
                "my-theme" => [
                    "Name" => "my-theme",
                    "Title" => "My Theme",
                    "Version" => "1.3.0.1",
                    "Author" => "Author",
                    "Author URI" => "http://www.author.com",
                    "UpdateURI" => "",
                    "Template" => "my-theme",
                    "Stylesheet" => "my-theme",
                ],
                "my-theme2" => [
                    "Name" => "my-theme2",
                    "Title" => "My Theme 2",
                    "Version" => "3.0",
                    "Author" => "Author",
                    "Author URI" => "http://www.author.com",
                    "UpdateURI" => "",
                    "Template" => "my-theme",
                    "Stylesheet" => "my-theme",
                ],
            ],
        ]),
        'translations' => "[]",
        'locale' => "[\"en_US\"]",
    ], [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(200);
    $response
        ->assertJsonCount(0, 'themes')
        ->assertJsonCount(2, 'no_update')
        ->assertJson([
            'themes' => [],
            'no_update' => [
                'my-theme' => [
                    'name' => 'My Theme',
                    'theme' => 'my-theme',
                    'new_version' => '1.2.1',
                    'url' => 'https://api.aspiredev.org/download/my-theme',
                    'package' => 'https://api.aspiredev.org/download/my-theme',
                    'requires' => null,
                    'requires_php' => '5.6',
                ],
                'my-theme2' => [
                    'name' => 'My Theme2',
                    'theme' => 'my-theme2',
                    'new_version' => '2.9',
                    'url' => 'https://api.aspiredev.org/download/my-theme2',
                    'package' => 'https://api.aspiredev.org/download/my-theme2',
                    'requires' => null,
                    'requires_php' => '5.6',
                ],
            ],
            'translations' => [],
        ]);
});

it('returns 400 when input is invalid', function () {
    // It was actually tricky to find a value that triggered validation failure: most syntax issues throw 500
    $this
        ->post('/themes/update-check/1.1', [
            'themes' => json_encode([
                "active" => 1,  // should be a string
                "themes" => [
                    "my-theme" => [
                        "Name" => "my-theme",
                        "Title" => "My Theme",
                        "Version" => "1.3.0.1",
                        "Author" => "Author",
                    ],
                ],
            ]),
            "locale" => "[]",
            "translations" => "[]",
        ])
        ->assertStatus(400)
        ->assertExactJson(['error' => 'The active field must be a string.']);
});

it('returns in serialized object format (v1.0)', function () {
    $content = $this
        ->post('/themes/update-check/1.0', [
            "locale" => "[]",
            "translations" => "[]",
            'themes' => json_encode([
                "active" => "my-theme",
                "themes" => [
                    "my-theme" => [
                        "Name" => "my-theme",
                        "Title" => "My Theme",
                        "Version" => "1.2.0",
                        "Author" => "Author",
                    ],
                ],
            ]),
        ])
        ->assertStatus(200)
        ->content();

    $response = unserialize($content);
    expect($response)->toBeObject();
    expect($response->themes)->toEqual([
        "my-theme" => [
            "name" => "My Theme",
            "theme" => "my-theme",
            "new_version" => "1.2.1",
            "url" => "https://api.aspiredev.org/download/my-theme",
            "package" => "https://api.aspiredev.org/download/my-theme",
            "requires" => null,
            "requires_php" => "5.6",
        ],
    ]);
});
