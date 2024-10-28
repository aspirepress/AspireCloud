<?php

use Illuminate\Testing\Fluent\AssertableJson;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
  ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');
function assertWpThemeBaseStructure($json)
{
    return $json
        ->has('name')
        ->has('slug')
        ->has('version')
        ->has('author')
        ->has('preview_url')
        ->has('screenshot_url')
        ->has('rating')
        ->has('num_ratings')
        ->has('homepage')
        ->has('description');
}
function assertWpThemeInfoBaseStructure($json)
{
    return $json
    ->has('name')
    ->has('slug')
    ->has('version')
    ->has('preview_url')
    ->has('screenshot_url')
    ->has('rating')
    ->has('num_ratings')
    ->has('homepage')
    ->has('sections')
    ->has('tags')
    ->has('download_link')
    ->has('last_updated')
    ->has('downloaded')
    ->has('last_updated_time')
    ->has('author');
}
function assertWpThemeAPIStructure1_1_query_themes($response)
{
    /*
      Asserts JSON structure of returned theme:

      "name": "Twenty Twenty-Three",
      "slug": "twentytwentythree",
      "version": "1.5",
      "preview_url": "https://wp-themes.com/twentytwentythree/",
      "author": "wordpressdotorg",
      "screenshot_url": "//ts.w.org/wp-content/themes/twentytwentythree/screenshot.png?ver=1.5",
      "rating": 68,
      "num_ratings": 62,
      "homepage": "https://wordpress.org/themes/twentytwentythree/",
      "description": "Twenty Twenty-Three is designed to take advantage of the new design tools introduced in WordPress 6.1. With a clean, blank base as a starting point, this default theme includes ten diverse style variations created by members of the WordPress community. Whether you want to build a complex or incredibly simple website, you can do it quickly and intuitively through the bundled styles or dive into creation and full customization yourself."
      "template": "jason-portfolio-resume"
      */

    return $response->assertJson(
        fn(AssertableJson $json) =>
        $json->has('info')->has(
            'themes',
            fn($json) =>
            $json->each(
                fn($theme) =>
                assertWpThemeBaseStructure($theme)
                    ->whereType('author', 'string')
            )
        )
    );

}


function assertWpThemeAPIStructure1_2_query_themes($response)
{
    /*
      Asserts JSON structure of returned theme:
      -- Note: author is expanded
      -- And additional fields are present
      "name": "Twenty Twenty-Three",
      "slug": "twentytwentythree",
      "version": "1.5",
      "preview_url": "https://wp-themes.com/twentytwentythree/",
      "author": "wordpressdotorg",
      "screenshot_url": "//ts.w.org/wp-content/themes/twentytwentythree/screenshot.png?ver=1.5",
      "rating": 68,
      "num_ratings": 62,
      "homepage": "https://wordpress.org/themes/twentytwentythree/",
      "description": "Twenty Twenty-Three is designed to take advantage of the new design tools introduced in WordPress 6.1. With a clean, blank base as a starting point, this default theme includes ten diverse style variations created by members of the WordPress community. Whether you want to build a complex or incredibly simple website, you can do it quickly and intuitively through the bundled styles or dive into creation and full customization yourself."
      "template": "jason-portfolio-resume"
      */

    return $response->assertJson(
        fn(AssertableJson $json) =>
        $json->has('info')->has(
            'themes',
            fn($json) =>
            $json->each(
                fn($theme) =>

                assertWpThemeBaseStructure($theme)

                    ->has('requires')
                    ->has('requires_php')
                    ->has('is_commercial')
                    ->has('external_support_url')
                    ->has('is_community')
                    ->has('external_repository_url')

                    ->whereType('author', 'array')
            )
        )
    );

}


function assertWpThemeAPIStructure1_1_theme_information($response)
{

    return $response->assertJson(
        fn(AssertableJson $json) =>
        assertWpThemeInfoBaseStructure($json)
        ->whereType('author', 'string')
    );

}

function assertWpThemeAPIStructure1_2_theme_information($response)
{

    return $response->assertJson(
        fn(AssertableJson $json) =>
        assertWpThemeInfoBaseStructure($json)
        ->has('requires')
        ->has('requires_php')
        ->has('is_commercial')
        ->has('external_support_url')
        ->has('is_community')
        ->has('external_repository_url')

        ->whereType('author', 'array')
    );

}

function assertWpPluginAPIStructure($response)
{
    return $response->assertJsonStructure([
        'name',
        'slug',
        'version',
        'author',
        'author_profile',
        'requires',
        'tested',
        'requires_php',
        'rating',
        'ratings' => [
            5,
            4,
            3,
            2,
            1,
        ],
        'num_ratings',
        'support_threads',
        'support_threads_resolved',
        'active_installs',
        'downloaded',
        'last_updated',
        'added',
        'homepage',
        'sections' => [
            'description',
            'installation',
            'changelog',
            'reviews',
        ],
        'download_link',
        'tags' => [],
        'versions',
        'donate_link',
        'contributors' => [
            '*' => [
                'profile',
                'avatar',
                'display_name',
            ],
        ],
        'screenshots',
    ]);
}


function assertWpPluginAPIStructureForSearch($response)
{
    return $response->assertJsonStructure([
        'info'    => [
            'page',
            'pages',
            'results',
        ],
        'plugins' => [
            '*' => [
                'name',
                'slug',
                'version',
                'author',
                'author_profile',
                'requires',
                'tested',
                'requires_php',
                'rating',
                'num_ratings',
                'ratings' => [
                    '1',
                    '2',
                    '3',
                    '4',
                    '5',
                ],
                'support_threads',
                'support_threads_resolved',
                'active_installs',
                'downloaded',
                'last_updated',
                'added',
                'homepage',
                'download_link',
                'tags',
                'donate_link',
                'short_description',
                'description',
                'icons'   => [
                    '1x',
                    '2x',
                ],
                'requires_plugins',
            ],
        ],
    ]);
}
