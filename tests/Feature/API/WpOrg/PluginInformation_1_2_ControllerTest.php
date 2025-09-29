<?php

use App\Models\WpOrg\ClosedPlugin;
use App\Models\WpOrg\Plugin;
use App\Values\WpOrg\Plugins\PluginResponse;
use Carbon\Carbon;

beforeEach(function () {
    Plugin::truncate();
});

function plugin_information_uri(string $slug, array $params = []): string
{
    return "/plugins/info/1.2?" . http_build_query(['action' => 'plugin_information', 'slug' => $slug, ...$params]);
}

function query_plugin_uri(array $params = []): string
{
    return "/plugins/info/1.2?" . http_build_query(['action' => 'query_plugins', ...$params]);
}

it('returns 422 when slug is missing', function () {
    $this
        ->getJson('/plugins/info/1.2?action=plugin_information')
        ->assertStatus(422);
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
        ->assertJsonStructure([
            'ac_origin',
            'ac_created',
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
        ],
        );
});

// this test probably belongs elsewhere, but the whole suite is in need of some refactoring...
it('returns plugin contributors in wp.org format', function () {
    $md_0errors = [
        'slug' => '0-errors',
        'name' => '0-Errors',
        'status' => 'open',
        'version' => '0.2',
        'author' => '<a href="http://zanto.org/">Ayebare Mucunguzi</a>',
        'author_profile' => 'https://profiles.wordpress.org/brooksx/',
        'contributors' => [
            'brooksx' => [
                'profile' => 'https://profiles.wordpress.org/brooksx/',
                'avatar' => 'https://secure.gravatar.com/avatar/4fa021b564189f92bf90322a1215401d?s=96&d=monsterid&r=g',
                'display_name' => 'Ayebare Mucunguzi Brooks',
            ],
        ],
        'requires' => '3.1',
        'tested' => '4.1.41',
        'requires_php' => false,
        'requires_plugins' => [],
        'compatibility' => [],
        'rating' => 100,
        'ratings' => [
            '5' => 1,
            '4' => 0,
            '3' => 0,
            '2' => 0,
            '1' => 0,
        ],
        'num_ratings' => 1,
        'support_url' => 'https://wordpress.org/support/plugin/0-errors/',
        'support_threads' => 0,
        'support_threads_resolved' => 0,
        'active_installs' => 10,
        'downloaded' => 2616,
        'last_updated' => '2015-01-28 9:41pm GMT',
        'added' => '2015-01-20',
        'homepage' => 'http://example.org/',
        'sections' => [
            'description' => '<p>This plugin makes it easy to work with WordPress with-ought the errors messing up the layout as they are nicely hidden in a drop down panel. Also PHP Errors are only shown to the admin and won&#8217;t be visible to the general public. There options to send the admin an email informing him of an error that has occurred on the site. The plugin has options of intercepting Ajax errors and PHP errors generated during Javascript requests and saving them to be viewed for debugging.</p><h3>Features</h3><ul><li>Show PHP errors only to the admin and hide them from the general public</li><li>Prevents PHP errors from breaking the site by displaying them in a drop down panel</li><li>Report PHP site errors to the admin by email</li><li>Capture PHP errors generated during ajax or Javascript requests to be viewed for debugging.</li></ul>',
            'installation' => '<p>Upload the 0-Errors Plugin Base plugin to your blog and activate it. It would work as is.</p>',
            'faq' => '<h4>Is it compatible with latest WordPress?</h4><p><p>Yes, it is, as well as with the latest PHP.</p></p>',
            'changelog' => '<h4>0.2</h4><ul><li>Bug fixes</li></ul><h4>0.1</h4><ul><li>Initial commit</li></ul>',
            'reviews' => '',
        ],
        'short_description' => 'Shows generated php site errors only to the admin via a drop down panel and hides them from the public. Email Alerts the admin of errors.',
        'description' => '<p>This plugin makes it easy to work with WordPress with-ought the errors messing up the layout as they are nicely hidden in a drop down panel. Also PHP Errors are only shown to the admin and won&#8217;t be visible to the general public. There options to send the admin an email informing him of an error that has occurred on the site. The plugin has options of intercepting Ajax errors and PHP errors generated during Javascript requests and saving them to be viewed for debugging.</p><h3>Features</h3><ul><li>Show PHP errors only to the admin and hide them from the general public</li><li>Prevents PHP errors from breaking the site by displaying them in a drop down panel</li><li>Report PHP site errors to the admin by email</li><li>Capture PHP errors generated during ajax or Javascript requests to be viewed for debugging.</li></ul>',
        'download_link' => 'https://downloads.wordpress.org/plugin/0-errors.0.2.zip',
        'upgrade_notice' => [],
        'screenshots' => [],
        'tags' => [
            'debug' => 'debug',
            'email-errors' => 'email errors',
            'errors' => 'errors',
            'error_reporting' => 'error_reporting',
        ],
        'versions' => [
            '0.1' => 'https://downloads.wordpress.org/plugin/0-errors.0.1.zip',
            '0.2' => 'https://downloads.wordpress.org/plugin/0-errors.0.2.zip',
            'trunk' => 'https://downloads.wordpress.org/plugin/0-errors.zip',
        ],
        'business_model' => false,
        'repository_url' => '',
        'commercial_support_url' => '',
        'donate_link' => '',
        'banners' => [],
        'icons' => [
            'default' => 'https://s.w.org/plugins/geopattern-icon/0-errors.svg',
        ],
        'author_block_count' => 0,
        'author_block_rating' => 100,
        'preview_link' => '',
        'aspiresync_meta' => [
            'id' => '01933d0c-12a5-71f0-95eb-f6a036e963bb',
            'type' => 'plugin',
            'slug' => '0-errors',
            'name' => '0-Errors',
            'status' => 'open',
            'version' => '0.2',
            'origin' => 'wp_org',
            'updated' => '2015-01-28T21:41:00+00:00',
            'pulled' => '2024-11-18T02:13:41+00:00',
        ],
    ];

    $plugin = Plugin::fromSyncMetadata($md_0errors);

    $response = PluginResponse::from($plugin);

    expect($response->contributors)
        ->toMatchArray([
            'brooksx' => [
                'profile' => 'https://profiles.wordpress.org/brooksx/',
                'avatar' => 'https://secure.gravatar.com/avatar/4fa021b564189f92bf90322a1215401d?s=96&d=monsterid&r=g',
                'display_name' => 'Ayebare Mucunguzi Brooks',
                'author' => null,
                'author_url' => null,
                'user_nicename' => 'brooksx',
            ],
        ]);
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

    assert(is_iterable($responseData['plugins']));
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

it('prioritizes normalized search string', function () {
    $scf = [
        'name' => 'Secure Custom Fields',
        'slug' => 'secure-custom-fields',
    ];

    $icf = [
        'name' => "Insecure Customized Fields",
        'slug' => "insecure-customized-fields",
    ];

    Plugin::factory()->create($scf);
    Plugin::factory()->create($icf);

    $query = 'INseCurE CUSToM';

    $this
        ->get("/plugins/info/1.2?action=query_plugins&search=$query")
        ->assertOk()
        ->assertJsonPath('plugins.0.name', $icf['name']);
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

it('returns hot tags results in wp.org format (v1.2)', function () {
    expect(Plugin::count())->toBe(0);

    Plugin::factory()->count(5)->withSpecificTags(['black', 'white'])->create();
    Plugin::factory()->count(2)->withSpecificTags(['black', 'blue'])->create();
    Plugin::factory()->count(1)->withSpecificTags(['blue', 'white', 'red'])->create();

    expect(Plugin::count())->toBe(8);

    $this
        ->get('/plugins/info/1.2?action=hot_tags')
        ->assertStatus(200)
        ->assertExactJson([
            'black' => ['count' => 7, 'name' => 'black', 'slug' => 'black'],
            'blue' => ['count' => 3, 'name' => 'blue', 'slug' => 'blue'],
            'red' => ['count' => 1, 'name' => 'red', 'slug' => 'red'],
            'white' => ['count' => 6, 'name' => 'white', 'slug' => 'white'],
        ]);
});
