<?php

use App\Models\WpOrg\ClosedPlugin;
use App\Models\WpOrg\Plugin;
use Carbon\Carbon;

it('returns 400 when slug is missing', function () {
    Plugin::factory(10)->create();
    $response = makeApiRequest('GET', '/plugins/info/1.2?action=plugin_information');

    $response->assertStatus(400)
        ->assertJson([
            'error' => 'Slug is required',
        ]);
});

it('returns 404 when plugin does not exist', function () {
    Plugin::factory(10)->create();
    $response = makeApiRequest('GET', '/plugins/info/1.2?action=plugin_information&slug=non-existent-plugin');

    $response->assertStatus(404)
        ->assertJson([
            'error' => 'Plugin not found',
        ]);
});

it('returns plugin information in wp.org format', function () {
    Plugin::factory()->create([
        'name' => 'JWT Authentication for WP-API',
        'slug' => 'jwt-authentication-for-wp-rest-api',
    ]);
    Plugin::factory(9)->create();

    $response = makeApiRequest('GET', '/plugins/info/1.2?action=plugin_information&slug=jwt-authentication-for-wp-rest-api');

    $response->assertStatus(200)
        ->assertJson([
            'name' => 'JWT Authentication for WP-API',
        ]);

    assertWpPluginAPIStructure($response);
});

it('returns closed plugin information in wp.org format', function () {
    $date = Carbon::parse('2021-02-03');
    ClosedPlugin::factory()->create([
        'name' => "Display Name If No Gravatar",
        'slug' => '0gravatar',
        'closed_date' => $date,
        'description' => 'test closed plugin',
        'reason' => 'author-request',
    ]);

    $response = makeApiRequest('GET', '/plugins/info/1.2?action=plugin_information&request[slug]=0gravatar');

    $response
        ->assertStatus(404)
        ->assertJson([
            'error' => 'closed',
            'name' => 'Display Name If No Gravatar',
            'slug' => '0gravatar',
            'description' => 'test closed plugin',
            'closed' => true,
            'closed_date' => $date->format('Y-m-d'),
            'reason' => 'author-request',
            'reason_text' => 'Author Request',
        ]);
});

it('returns search results by tag in wp.org format', function () {
    $tags = ['jwt', 'authentication', 'rest api'];
    $tagToQuery = 'jwt';

    Plugin::factory(8)->create();
    Plugin::factory()->count(2)->withSpecificTags($tags)->create();

    expect(Plugin::query()->count())->toBe(10);

    $response = makeApiRequest('GET', '/plugins/info/1.2?action=query_plugins&tag=' . $tagToQuery);

    $response->assertStatus(200);
    assertWpPluginAPIStructureForSearch($response);

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

    foreach ($responseData['plugins'] as $plugin) {
        expect($plugin['tags'])->toContain($tagToQuery);
    }
});

it('returns search results by query string in wp.org format', function () {
    $query = 'jwt';
    Plugin::factory()->create([
        'name' => 'JWT Authentication for WP-API',
        'slug' => 'jwt-authentication-for-wp-rest-api',
    ]);

    Plugin::factory(9)->create();

    expect(Plugin::query()->count())->toBe(10);

    $response = makeApiRequest('GET', '/plugins/info/1.2?action=query_plugins&search=' . $query);

    $response->assertStatus(200);
    assertWpPluginAPIStructureForSearch($response);

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

it('returns search results by tag and author in wp.org format', function () {
    $tags = ['jwt', 'authentication', 'rest api'];
    $tagToQuery = 'jwt';
    $author = 'tmeister';

    Plugin::factory(9)->create();
    Plugin::factory()->count(1)
        ->withSpecificTags($tags)->create([
            'author' => $author,
        ]);

    expect(Plugin::query()->count())->toBe(10);

    $response = makeApiRequest('GET', '/plugins/info/1.2?action=query_plugins&tag=' . $tagToQuery . '&author=' . $author);

    $response->assertStatus(200);
    assertWpPluginAPIStructureForSearch($response);

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

    Plugin::factory(10)->create();
    expect(Plugin::query()->count())->toBe(10);

    $response = makeApiRequest('GET', '/plugins/info/1.2?action=query_plugins&per_page=' . $perPage . '&page=' . $page);

    $response->assertStatus(200);
    assertWpPluginAPIStructureForSearch($response);

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
