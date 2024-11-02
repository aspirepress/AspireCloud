<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

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

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
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
    return $response->assertJson(
        fn(AssertableJson $json) => $json->has('info')->has(
            'themes',
            fn($json) => $json->each(
                fn($theme) => assertWpThemeBaseStructure($theme)
                    ->whereType('author', 'string')
            )
        )
    );
}

function assertWpThemeAPIStructure1_2_query_themes($response)
{
    return $response->assertJson(
        fn(AssertableJson $json) => $json->has('info')->has(
            'themes',
            fn($json) => $json->each(
                fn($theme) => assertWpThemeBaseStructure($theme)
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
        fn(AssertableJson $json) => assertWpThemeInfoBaseStructure($json)
            ->whereType('author', 'string')
    );
}

function assertWpThemeAPIStructure1_2_theme_information($response)
{
    return $response->assertJson(
        fn(AssertableJson $json) => assertWpThemeInfoBaseStructure($json)
            ->has('requires')
            ->has('requires_php')
            ->has('is_commercial')
            ->has('external_support_url')
            ->has('is_community')
            ->has('external_repository_url')
            ->has('reviews_url')
            ->has('creation_time')
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
        'ratings'      => [
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
        'sections'     => [
            'description',
            'installation',
            'changelog',
            'reviews',
        ],
        'download_link',
        'tags'         => [],
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

/**
 * Helper function to make an authenticated request or not
 * based on the configuration.
 * @param mixed $method
 * @param mixed $uri
 * @param mixed $data
 * @param mixed $headers
 */
function makeApiRequest($method, $uri, $data = [], $headers = [])
{
    $isAuthEnabled = config('app.aspire_press.api_authentication_enable');
    $testCase      = test();

    if ($isAuthEnabled) {
        $user     = User::factory()->create();
        $testCase = $testCase->actingAs($user);
    }

    if (Str::lower($method) === 'post') {
        return $testCase->post($uri, $data, $headers);
    }

    return $testCase->{$method}($uri);
}
