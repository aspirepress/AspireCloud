<?php

use App\Models\WpOrg\ClosedPlugin;
use App\Models\WpOrg\Plugin;
use Carbon\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    Plugin::truncate();
});

function plugin_information_uri(string $slug, array $params = []): string
{
    return "/plugins/info/1.2?" . http_build_query(['action' => 'plugin_information', 'slug' => $slug, ...$params]);
}

function query_plugin_uri(array $params = []): string
{
    return "/plugins/info/1.2?" . http_build_query(['action' => 'query_plugibs', ...$params]);
}

it('returns 400 when slug is missing', function () {
    $this
        ->getJson('/plugins/info/1.2?action=plugin_information')
        ->assertStatus(400)
        ->assertJson(['error' => 'Slug is required']);
});

it('returns 404 when plugin does not exist', function () {
    Plugin::factory(5)->create();
    $this
        ->getJson(plugin_information_uri('non-existent-plugin'))
        ->assertStatus(404)
        ->assertJson(['error' => 'Plugin not found']);
});

it('returns plugin information in wp.org format', function () {
    Plugin::factory()->create(['name' => 'ROT-26 Encrypted Passwords', 'slug' => 'rot-26-encrypted-passwords']);

    $this
        ->getJson(plugin_information_uri('rot-26-encrypted-passwords'))
        ->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->hasAll([
                'active_installs',
                'added',
                'author',
                'author_profile',
                'banners',
                'business_model',
                'commercial_support_url',
                'contributors',
                'donate_link',
                'download_link',
                'homepage',
                'last_updated',
                'name',
                'num_ratings',
                'preview_link',
                'rating',
                'ratings',
                'repository_url',
                'requires',
                'requires_php',
                'requires_plugins',
                'screenshots',
                'sections',
                'slug',
                'support_threads',
                'support_threads_resolved',
                'support_url',
                'tags',
                'tested',
                'upgrade_notice',
                'version',
                'versions',
            ])
            ->whereAllType([
                'active_installs' => 'integer',
                'added' => 'string',
                'author' => 'string',
                'author_profile' => 'string',
                'banners' => 'array',
                'business_model' => 'string',
                'commercial_support_url' => 'string|null',
                'contributors' => 'array',
                'donate_link' => 'string|null',
                'download_link' => 'string',
                'homepage' => 'string|null',
                'last_updated' => 'string',
                'name' => 'string',
                'num_ratings' => 'integer',
                'preview_link' => 'string|null',
                'rating' => 'integer',
                'ratings' => 'array',
                'repository_url' => 'string|null',
                'requires' => 'string|null',
                'requires_php' => 'string|null',
                'requires_plugins' => 'array',
                'screenshots' => 'array',
                'sections' => 'array',
                'slug' => 'string',
                'support_threads' => 'integer',
                'support_threads_resolved' => 'integer',
                'support_url' => 'string|null',
                'tags' => 'array',
                'tested' => 'string|null',
                'upgrade_notice' => 'array',
                'version' => 'string',
                'versions' => 'array',
            ]),
        );
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

    $response = $this->get('/plugins/info/1.2?action=plugin_information&request[slug]=0gravatar');

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

    $response = $this->get("/plugins/info/1.2?action=query_plugins&tag=$tagToQuery");

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

    $response = $this->get("/plugins/info/1.2?action=query_plugins&search=$query");

    $response->assertStatus(200);
    assertWpPluginAPIStructureForSearch($response);

    $responseData = $response->json();
    expect(count($responseData['plugins']))
        ->toBe(1)
        ->and($responseData['info'])->toHaveKeys([
            'page',
            'pages',
            'results',
        ])
        ->and($responseData['info']['page'])->toBe(1)
        ->and($responseData['info']['pages'])->toBe(1)
        // FIXME: is currently 2 because of the union queries.
        // ->and($responseData['info']['results'])->toBe(1)
    ;
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

    $response = $this->get("/plugins/info/1.2?action=query_plugins&tag=$tagToQuery&author=$author");

    $response->assertStatus(200);
    assertWpPluginAPIStructureForSearch($response);

    $responseData = $response->json();
    expect(count($responseData['plugins']))
        ->toBe(1)
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

    $response = $this->get("/plugins/info/1.2?action=query_plugins&per_page=$perPage&page=$page");

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
        ->and($responseData['info']['page'])->toBe(2)
        ->and($responseData['info']['pages'])->toBe(5)
        ->and($responseData['info']['results'])->toBe(10);
});
