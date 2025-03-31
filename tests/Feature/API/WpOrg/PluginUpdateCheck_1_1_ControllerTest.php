<?php

use App\Models\WpOrg\Plugin;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Safe\json_encode;

uses(RefreshDatabase::class);

it('returns an empty response for empty requests', function () {
    $this
        ->withHeader('Accept', 'application/json')
        ->post('/plugins/update-check/1.1', [
            'plugins' => json_encode(['plugins' => []]),
            'translations' => json_encode([]),
            'locale' => json_encode([]),
        ])
        ->assertStatus(200)
        ->assertExactJson(['plugins' => [], 'translations' => []]);
});

it('returns plugin updates from minimal input', function () {
    Plugin::factory(['slug' => 'frobnicator', 'version' => '1.2.3'])->create();
    Plugin::factory(['slug' => 'transmogrifier', 'version' => '0.5'])->create();

    $this
        ->withHeader('Accept', 'application/json')
        ->post('/plugins/update-check/1.1', [
            'plugins' => json_encode([
                'plugins' => [
                    'frobnicator' => ['Version' => '1.0.2'],    // out of date
                    'transmogrifier' => ['Version' => '0.5'],   // up to date
                ],
            ]),
            'translations' => json_encode([]),
            'locale' => json_encode([]),
        ])
        ->assertStatus(200)
        ->assertJson([
            'plugins' => [
                'frobnicator' => [
                    'plugin' => 'frobnicator',
                    'slug' => 'frobnicator',
                    'new_version' => '1.2.3',
                ],
            ],
        ])
        ->assertJsonMissingPath('plugins.transmogrifier')
        ->assertJsonStructure([
            'plugins' => [
                '*' => [
                    'banners' => ['high', 'low'],
                    'banners_rtl',
                    'compatibility' => [
                        'php' => ['minimum', 'recommended'],
                        'wordpress' => ['maximum', 'minimum', 'tested'],
                    ],
                    'icons' => ['1x', '2x'],
                    'id',
                    'new_version',
                    'package',
                    'plugin',
                    'requires',
                    'requires_php',
                    'requires_plugins',
                    'slug',
                    'tested',
                    'url',
                ],
            ],
            'translations',
        ]);
});
