<?php

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
