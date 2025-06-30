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
                    'frobnicator/frobber.php' => ['Version' => '1.0.2'],    // out of date
                    'transmogrifier.php' => ['Version' => '0.5'],   // up to date
                ],
            ]),
            'translations' => json_encode([]),
            'locale' => json_encode([]),
        ])
        ->assertStatus(200)
        ->assertJson([
            'plugins' => [
                'frobnicator/frobber.php' => [
                    'plugin' => 'frobnicator/frobber.php',
                    'slug' => 'frobnicator',
                    'new_version' => '1.2.3',
                ],
            ],
        ])
        ->assertJsonMissingPath('plugins.transmogrifier')
        ->assertJsonMissingPath('plugins.no_update')
        ->assertExactJsonStructure([
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

it('uses defaults for malformed requests', function () {
    Plugin::factory(['slug' => 'frobnicator', 'version' => '1.2.3'])->create();
    Plugin::factory(['slug' => 'transmogrifier', 'version' => '0.5'])->create();

    $this
        ->withHeader('Accept', 'application/json')
        ->post('/plugins/update-check/1.1', [
            'plugins' => json_encode([
                'plugins' => [
                    'frobnicator/frobber.php' => ['Version' => '1.0.2'],    // out of date
                    'transmogrifier.php' => ['Version' => '0.5'],   // up to date
                ],
            ]),
            'translations' => '', // becomes null!
            'locale' => 'not_json',
        ])
        ->assertStatus(200)
        ->assertJson([
            'plugins' => [
                'frobnicator/frobber.php' => [
                    'plugin' => 'frobnicator/frobber.php',
                    'slug' => 'frobnicator',
                    'new_version' => '1.2.3',
                ],
            ],
        ])
        ->assertJsonMissingPath('plugins.transmogrifier')
        ->assertJsonMissingPath('plugins.no_update')
        ->assertExactJsonStructure([
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

it('includes no_update when all=true', function () {
    Plugin::factory(['slug' => 'frobnicator', 'version' => '1.2.3'])->create();
    Plugin::factory(['slug' => 'transmogrifier', 'version' => '0.5'])->create();

    $this
        ->withHeader('Accept', 'application/json')
        ->post('/plugins/update-check/1.1?all=true', [
            'plugins' => json_encode([
                'plugins' => [
                    'frobnicator/frobber.php' => ['Version' => '1.0.2'],    // out of date
                    'transmogrifier.php' => ['Version' => '0.5'],   // up to date
                ],
            ]),
            'translations' => json_encode([]),
            'locale' => json_encode([]),
        ])
        ->assertStatus(200)
        ->assertJson([
            'plugins' => [
                'frobnicator/frobber.php' => [
                    'plugin' => 'frobnicator/frobber.php',
                    'slug' => 'frobnicator',
                    'new_version' => '1.2.3',
                ],
            ],
        ])
        ->assertJsonMissingPath('plugins.transmogrifier')
        ->assertExactJsonStructure([
            'plugins' => [
                'frobnicator/frobber.php' => [
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
            'no_update' => [
                'transmogrifier.php' => [
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
