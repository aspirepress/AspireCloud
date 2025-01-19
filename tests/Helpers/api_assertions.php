<?php

declare(strict_types=1);

use Illuminate\Testing\Fluent\AssertableJson;

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
                    ->whereType('author', 'string'),
            ),
        ),
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
                    ->whereType('author', 'array'),
            ),
        ),
    );
}

function assertWpThemeAPIStructure1_1_theme_information($response)
{
    return $response->assertJson(
        fn(AssertableJson $json) => assertWpThemeInfoBaseStructure($json)
            ->whereType('author', 'string'),
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
            ->whereType('author', 'array'),
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
