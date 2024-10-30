<?php

use App\Models\WpOrg\Plugin;

beforeEach(function () {
    Plugin::factory()->create([
        'name' => 'JWT Auth',
        'slug' => 'jwt-auth',
        'tags' => ['authentication', 'jwt', 'api'],
    ]);

    Plugin::factory()->create([
        'name' => 'JWT Authentication for WP-API',
        'slug' => 'jwt-authentication-for-wp-rest-api',
        'tags' => ['jwt', 'api', 'rest-api'],
        'author' => 'tmeister',
    ]);

    Plugin::factory()->count(8)->create();
});

it('returns 400 when slug is missing', function () {
    $response = $this->getJson('/plugins/info/1.2?action=plugin_information');

    $response->assertStatus(400)
        ->assertJson([
            'error' => 'Slug is required',
        ]);
});

it('returns 404 when plugin does not exist', function () {
    $response = $this->getJson('/plugins/info/1.2?action=plugin_information&slug=non-existent-plugin');

    $response->assertStatus(404)
        ->assertJson([
            'error' => 'Plugin not found',
        ]);
});

it('returns plugin information in wp.org format', function () {
    $response = $this->getJson('/plugins/info/1.2?action=plugin_information&slug=jwt-authentication-for-wp-rest-api');

    $response->assertStatus(200)
        ->assertJson([
            'name' => 'JWT Authentication for WP-API',
        ]);

    assertWpPluginAPIStructure($response);
});

it('returns search results by tag in wp.org format', function () {
    $tag = 'jwt';

    expect(Plugin::query()->count())->toBe(10);

    $jwtPlugins = Plugin::query()->where('tags', 'ilike', '%' . $tag . '%')->count();
    expect($jwtPlugins)->toBe(2);

    $response = $this->getJson('/plugins/info/1.2?action=query_plugins&tag=' . $tag);

    $response->assertStatus(200);
    assertWpPluginAPIStructureForSearch($response);

    // Get the response data
    $responseData = $response->json();
    expect(count($responseData['plugins']))
        ->toBe(2)
        ->and($responseData['info'])->toHaveKeys([
            'page',
            'pages',
            'results',
        ])
        ->and($responseData['info']['page'])->toBe(1)
        ->and($responseData['info']['pages'])->toBe(1)
        ->and($responseData['info']['results'])->toBe(2);

    // Assert that each plugin has the 'jwt' tag
    foreach ($responseData['plugins'] as $plugin) {
        expect($plugin['tags'])->toContain($tag);
    }
});

it('returns search results by query string in wp.org format', function () {
    // Set the query string to search for
    $query = 'jwt';
    expect(Plugin::query()->count())->toBe(10);

    $response = $this->getJson('/plugins/info/1.2?action=query_plugins&search=' . $query);

    $response->assertStatus(200);
    assertWpPluginAPIStructureForSearch($response);

    // Get the response data
    $responseData = $response->json();
    expect(count($responseData['plugins']))->toBe(2)
        ->and($responseData['info'])->toHaveKeys([
            'page',
            'pages',
            'results',
        ])
        ->and($responseData['info']['page'])->toBe(1)
        ->and($responseData['info']['pages'])->toBe(1)
        ->and($responseData['info']['results'])->toBe(2);
});

it('returns search results by tag and author in wp.org format', function () {
    // Set the query string to search for
    $tag = 'jwt';
    $author = 'tmeister';

    expect(Plugin::query()->count())->toBe(10);

    $response = $this->getJson('/plugins/info/1.2?action=query_plugins&tag=' . $tag . '&author=' . $author);

    $response->assertStatus(200);
    assertWpPluginAPIStructureForSearch($response);

    // Get the response data
    $responseData = $response->json();
    expect(count($responseData['plugins']))->toBe(1)
        ->and($responseData['info'])->toHaveKeys([
            'page',
            'pages',
            'results',
        ])
        ->and($responseData['info']['page'])->toBe(1)
        ->and($responseData['info']['pages'])->toBe(1)
        ->and($responseData['info']['results'])->toBe(1);
});

it('returns a valid pagination', function () {
    $perPage = 2;
    $page = 2;

    expect(Plugin::query()->count())->toBe(10);

    $response = $this->getJson('/plugins/info/1.2?action=query_plugins&per_page=' . $perPage . '&page=' . $page);

    $response->assertStatus(200);
    assertWpPluginAPIStructureForSearch($response);

    // Get the response data
    $responseData = $response->json();
    expect(count($responseData['plugins']))->toBe(2)
        ->and($responseData['info'])->toHaveKeys([
            'page',
            'pages',
            'results',
        ])
        ->and($responseData['info']['page'])->toBe(2)
        ->and($responseData['info']['pages'])->toBe(5)
        ->and($responseData['info']['results'])->toBe(10);
});
