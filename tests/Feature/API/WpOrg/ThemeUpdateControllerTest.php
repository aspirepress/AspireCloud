<?php

use App\Models\WpOrg\Plugin;
use App\Models\WpOrg\Theme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;

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
        'version' => '1.2.1',
        'download_link' => 'https://downloads.wp/my-theme',
        'requires_php' => '5.6',
        'last_updated' => '2021-01-11 12:00:00',
        'creation_time' => '2021-01-01 12:00:00',
        'preview_url' => 'https://wp-themes.com/my-theme',
        'screenshot_url' => 'https://wp-themes.com/my-theme/screenshot.png',
        'ratings' => [5,4,3,2,1,2],
        'rating' => 5,
        'num_ratings' => 6,
        'reviews_url' => 'https://wp-themes.com/my-theme/reviews',
        'downloaded' => 1000,
        'active_installs' => 100,
        'homepage' => 'https://wp-themes.com/my-theme',
        'sections' => [],
        'tags' => ['black','white','red','blue'],
        'versions' => ['1.2.1','1.2.0','1.1.0'],
        'requires' => ['php' => '5.6','wp' => '5.0'],
        'is_commercial' => false,
        'external_support_url' => null,
        'is_community' => true,
        'external_repository_url' => 'https://test.com',
        'author_id' => $authorId,
    ]);
    Theme::create([
        'slug' => 'my-theme2',
        'name' => 'My Theme2',
        'version' => '2.9',
        'download_link' => 'https://downloads.wp/my-theme2',
        'requires_php' => '5.6',
        'last_updated' => '2021-01-11 12:00:00',
        'creation_time' => '2021-01-01 12:00:00',
        'preview_url' => 'https://wp-themes.com/my-theme2',
        'screenshot_url' => 'https://wp-themes.com/my-theme2/screenshot.png',
        'ratings' => [5,4,3,2,1,2],
        'rating' => 5,
        'num_ratings' => 6,
        'reviews_url' => 'https://wp-themes.com/my-theme2/reviews',
        'downloaded' => 1000,
        'active_installs' => 100,
        'homepage' => 'https://wp-themes.com/my-theme2',
        'sections' => [],
        'tags' => ['black','white','red','blue'],
        'versions' => ['1.2.1','1.2.0','1.1.0'],
        'requires' => ['php' => '5.6','wp' => '5.0'],
        'is_commercial' => false,
        'external_support_url' => null,
        'is_community' => true,
        'external_repository_url' => 'https://test.com',
        'author_id' => $authorId,
    ]);

    // Theme::factory()->count(8)->create();
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
        ]), 'translations' => "[]",
        'locale' => "[\"en_US\"]",
    ], [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(200);
    $response->assertJsonCount(1, 'themes')
         ->assertJsonCount(1, 'no_update')
         ->assertJsonStructure([
             'themes' => [
                 'my-theme' => [
                     'name',
                     'new_version',
                     'package',
                     'requires',
                     'requires_php',
                     'theme',
                     'url',
                 ],
             ],
             'no_update' => [
                 'my-theme2' => [
                     'name',
                     'new_version',
                     'package',
                     'requires',
                     'requires_php',
                     'theme',
                     'url',
                 ],
             ],
             'translations',
         ])
         ->assertJsonPath('themes.my-theme.name', 'My Theme')
         ->assertJsonPath('themes.my-theme.new_version', '1.2.1')
         ->assertJsonPath('themes.my-theme.theme', 'my-theme')
         ->assertJsonPath('no_update.my-theme2.name', 'My Theme2')
         ->assertJsonPath('no_update.my-theme2.new_version', '2.9')
         ->assertJsonPath('no_update.my-theme2.theme', 'my-theme2');
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
        ]), 'translations' => "[]",
        'locale' => "[\"en_US\"]",
    ], [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(200);
    $response->assertJsonCount(0, 'themes')
         ->assertJsonCount(2, 'no_update')
         ->assertJsonStructure([
             'themes',
             'no_update' => [
                 'my-theme' => [
                     'name',
                     'new_version',
                     'package',
                     'requires',
                     'requires_php',
                     'theme',
                     'url',
                 ],
                 'my-theme2' => [
                     'name',
                     'new_version',
                     'package',
                     'requires',
                     'requires_php',
                     'theme',
                     'url',
                 ],
             ],
             'translations',
         ])
         ->assertJsonPath('no_update.my-theme.name', 'My Theme')
         ->assertJsonPath('no_update.my-theme.new_version', '1.2.1')
         ->assertJsonPath('no_update.my-theme.theme', 'my-theme')
         ->assertJsonPath('no_update.my-theme2.name', 'My Theme2')
         ->assertJsonPath('no_update.my-theme2.new_version', '2.9')
         ->assertJsonPath('no_update.my-theme2.theme', 'my-theme2');
});
