<?php

declare(strict_types=1);

use Illuminate\Testing\TestResponse;

function assertWpPluginAPIStructureForSearch(TestResponse $response): TestResponse
{
    return $response->assertJsonStructure([
        'info' => [
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
                'icons' => [
                    '1x',
                    '2x',
                ],
                'requires_plugins',
            ],
        ],
    ]);
}
