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
        ->assertExactJsonStructure([
            'plugins' => [
                'frobnicator' => [
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
                'transmogrifier' => [
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


// '{\n
//     "no_update": {\n
//         "transmogrifier": {\n
//             "banners": {\n
//                 "high": "https://via.placeholder.com/1544x500.png/0022cc?text=molestias",\n
//                 "low": "https://via.placeholder.com/772x250.png/001199?text=minima"\n
//             },\n
//             "banners_rtl": [],\n
//             "compatibility": {\n
//                 "php": {\n
//                     "minimum": "7.4",\n
//                     "recommended": "8.2"\n
//                 },\n
//                 "wordpress": {\n
//                     "maximum": "8.2.99",\n
//                     "minimum": "2.16.32",\n
//                     "tested": "8.44.14"\n
//                 }\n
//             },\n
//             "icons": {\n
//                 "1x": "https://via.placeholder.com/128x128.png/006699?text=laboriosam",\n
//                 "2x": "https://via.placeholder.com/256x256.png/00ee11?text=ut"\n
//             },\n
//             "id": "w.org/plugins/transmogrifier",\n
//             "new_version": "0.5",\n
//             "package": "http://www.vonrueden.com/iusto-voluptatem-delectus-tempora-placeat-tempora-exercitationem-ducimus-blanditiis.html",\n
//             "plugin": "transmogrifier",\n
//             "requires": "3.22.80",\n
//             "requires_php": "8.2",\n
//             "requires_plugins": [],\n
//             "slug": "transmogrifier",\n
//             "tested": "WordPress 4.3.39",\n
//             "url": "https://wordpress.org/plugins/transmogrifier/"\n
//         }\n
//     },\n
//     "plugins": {\n
//         "frobnicator": {\n
//             "banners": {\n
//                 "high": "https://via.placeholder.com/1544x500.png/00ee22?text=laudantium",\n
//                 "low": "https://via.placeholder.com/772x250.png/0077cc?text=soluta"\n
//             },\n
//             "banners_rtl": [],\n
//             "compatibility": {\n
//                 "php": {\n
//                     "minimum": "8.1",\n
//                     "recommended": "8.0"\n
//                 },\n
//                 "wordpress": {\n
//                     "maximum": "9.46.6",\n
//                     "minimum": "1.4.31",\n
//                     "tested": "9.91.94"\n
//                 }\n
//             },\n
//             "icons": {\n
//                 "1x": "https://via.placeholder.com/128x128.png/00ffff?text=minima",\n
//                 "2x": "https://via.placeholder.com/256x256.png/0077bb?text=quia"\n
//             },\n
//             "id": "w.org/plugins/frobnicator",\n
//             "new_version": "1.2.3",\n
//             "package": "http://shields.com/",\n
//             "plugin": "frobnicator",\n
//             "requires": "2.64.58",\n
//             "requires_php": "7.3",\n
//             "requires_plugins": [],\n
//             "slug": "frobnicator",\n
//             "tested": "WordPress 6.4.16",\n
//             "url": "https://wordpress.org/plugins/frobnicator/"\n
//         }\n
//     },\n
//     "translations": []\n
// }'
