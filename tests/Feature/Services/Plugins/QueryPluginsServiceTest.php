<?php

declare(strict_types=1);

use App\Models\WpOrg\Plugin;
use App\Services\Plugins\QueryPluginsService;
use App\Values\WpOrg\Plugins\QueryPluginsRequest;
use Illuminate\Database\Eloquent\Builder;

beforeEach(function () {
    Plugin::truncate();
});

test('queryPlugins with search returns matching plugins', function () {
    // Create plugins with specific names
    Plugin::factory()->create(['name' => 'Test Plugin', 'slug' => 'test-plugin']);
    Plugin::factory()->create(['name' => 'Another Plugin', 'slug' => 'another-plugin']);
    Plugin::factory(3)->create();

    // Create the service
    $service = new QueryPluginsService();

    // Create a request with search
    $request = new QueryPluginsRequest(
        search: 'Test',
        page: 1,
        per_page: 10,
    );

    // Query plugins
    $response = $service->queryPlugins($request);

    // Assert the response contains the matching plugin
    expect($response->plugins)
        ->toHaveCount(1)
        ->and($response->plugins->first()->name)->toBe('Test Plugin');
});

test('queryPlugins with tag returns plugins with that tag', function () {
    // Create plugins with specific tags
    Plugin::factory()->count(2)->withSpecificTags(['security'])->create();
    Plugin::factory()->count(3)->withSpecificTags(['performance'])->create();
    Plugin::factory(5)->create();

    // Create the service
    $service = new QueryPluginsService();

    // Create a request with tag
    $request = new QueryPluginsRequest(
        tags: ['security'],
        page: 1,
        per_page: 10,
    );

    // Query plugins
    $response = $service->queryPlugins($request);

    // Assert the response contains only plugins with the security tag
    expect($response->plugins)->toHaveCount(2);
    foreach ($response->plugins as $plugin) {
        expect($plugin->tags)->toContain('security');
    }
});

test('queryPlugins with author returns plugins by that author', function () {
    // Create plugins with specific authors
    Plugin::factory()->create(['author' => 'JohnDoe']);
    Plugin::factory()->create(['author' => 'JaneDoe']);
    Plugin::factory(3)->create();

    // Create the service
    $service = new QueryPluginsService();

    // Create a request with author
    $request = new QueryPluginsRequest(
        author: 'JohnDoe',
        page: 1,
        per_page: 10,
    );

    // Query plugins
    $response = $service->queryPlugins($request);

    // Assert the response contains only plugins by JohnDoe
    expect($response->plugins)
        ->toHaveCount(1)
        ->and($response->plugins->first()->author)->toBe('JohnDoe');
});

test('queryPlugins with browse parameter sorts plugins correctly', function () {
    // Create plugins with different attributes
    Plugin::factory()->create([
        'name' => 'New Plugin',
        'added' => now(),
        'last_updated' => now()->subDays(10),
        'rating' => 80,
        'active_installs' => 1000,
    ]);
    Plugin::factory()->create([
        'name' => 'Old Plugin',
        'added' => now()->subDays(30),
        'last_updated' => now(),
        'rating' => 90,
        'active_installs' => 5000,
    ]);

    // Create the service
    $service = new QueryPluginsService();

    // Test 'new' browse parameter
    $newRequest = new QueryPluginsRequest(
        browse: 'new',
        page: 1,
        per_page: 10,
    );
    $newResponse = $service->queryPlugins($newRequest);
    expect($newResponse->plugins->first()->name)->toBe('New Plugin');

    // Test 'updated' browse parameter
    $updatedRequest = new QueryPluginsRequest(
        browse: 'updated',
        page: 1,
        per_page: 10,
    );
    $updatedResponse = $service->queryPlugins($updatedRequest);
    expect($updatedResponse->plugins->first()->name)->toBe('Old Plugin');

    // Test 'top-rated' browse parameter
    $ratedRequest = new QueryPluginsRequest(
        browse: 'top-rated',
        page: 1,
        per_page: 10,
    );
    $ratedResponse = $service->queryPlugins($ratedRequest);
    expect($ratedResponse->plugins->first()->name)->toBe('Old Plugin');

    // Test 'popular' browse parameter (default)
    $popularRequest = new QueryPluginsRequest(
        browse: 'popular',
        page: 1,
        per_page: 10,
    );
    $popularResponse = $service->queryPlugins($popularRequest);
    expect($popularResponse->plugins->first()->name)->toBe('Old Plugin');
});

test('applySearchWeighted returns a query with weighted search conditions', function () {
    // Create a base query
    $query = Plugin::query();

    // Apply weighted search
    $weightedQuery = QueryPluginsService::applySearchWeighted($query, 'test', new QueryPluginsRequest());

    // Get the SQL for inspection
    $sql = $weightedQuery->toSql();

    // Assert that the query contains weighted search conditions
    expect($sql)
        ->toContain('score')
        ->and($sql)->toContain('weighted_plugins')
        ->and($sql)->toContain('order by');
});

test('applyAuthor adds author condition to the query', function () {
    // Create a base query
    $query = Plugin::query();

    // Apply author
    QueryPluginsService::applyAuthor($query, 'JohnDoe');

    // Get the SQL for inspection
    $sql = $query->toSql();

    // Assert that the query contains author condition
    expect($sql)->toContain('author');
});

test('applyTag adds tag condition to the query', function () {
    // Create a base query
    $query = Plugin::query();

    // Apply tag
    QueryPluginsService::applyTag($query, 'security');

    // Get the SQL for inspection
    $sql = $query->toSql();

    // Assert that the query contains tag condition
    expect($sql)
        ->toContain('exists')
        ->and($sql)->toContain('plugin_tags');
});

test('applyBrowse adds sorting to the query', function () {
    // Create a base query
    $query = Plugin::query();

    // Apply browse
    QueryPluginsService::applyBrowse($query, 'popular');

    // Get the SQL for inspection
    $sql = $query->toSql();

    // Assert that the query contains sorting
    expect($sql)->toContain('order by');
});

test('browseToSortColumn returns correct column for each browse parameter', function () {
    expect(QueryPluginsService::browseToSortColumn('new'))
        ->toBe('added')
        ->and(QueryPluginsService::browseToSortColumn('updated'))->toBe('last_updated')
        ->and(QueryPluginsService::browseToSortColumn('top-rated'))->toBe('rating')
        ->and(QueryPluginsService::browseToSortColumn('featured'))->toBe('rating')
        ->and(QueryPluginsService::browseToSortColumn('popular'))->toBe('active_installs')
        ->and(QueryPluginsService::browseToSortColumn(null))->toBe('active_installs');
});

test('normalizeSearchString handles various inputs correctly', function () {
    expect(QueryPluginsService::normalizeSearchString(null))
        ->toBeNull()
        ->and(QueryPluginsService::normalizeSearchString(''))->toBe('')
        ->and(QueryPluginsService::normalizeSearchString('  test  '))->toBe('test')
        ->and(QueryPluginsService::normalizeSearchString('test search'))->toBe('test search')
        ->and(QueryPluginsService::normalizeSearchString('test@example.com'))->toBe('test@example.com')
        ->and(QueryPluginsService::normalizeSearchString('test*search'))->toBe('test search');
});

test('applySearchWeighted prioritizes relevance over install count', function () {
    // Create plugins with different install counts and names
    Plugin::factory()->create([
        'name' => 'Exact Match Plugin',
        'slug' => 'exact-match-plugin',
        'active_installs' => 1000,
    ]);

    Plugin::factory()->create([
        'name' => 'Another Plugin',
        'slug' => 'another-plugin',
        'short_description' => 'This is a plugin that has exact match in description',
        'active_installs' => 10000, // 10x more installs
    ]);

    // Create the service
    $service = new QueryPluginsService();

    // Create a request with search for "exact match"
    $request = new QueryPluginsRequest(
        search: 'exact match',
        page: 1,
        per_page: 10,
    );

    // Query plugins
    $response = $service->queryPlugins($request);

    // The first plugin should be "Exact Match Plugin" despite having fewer installs
    // because it has higher relevance (name exact match)
    expect($response->plugins)
        ->toHaveCount(2)
        ->and($response->plugins->first()->name)->toBe('Exact Match Plugin')
        ->and($response->plugins->last()->name)->toBe('Another Plugin');
});

test('applySearchWeighted with name similarity vs description match', function () {
    // Create plugins with different attributes
    Plugin::factory()->create([
        'name' => 'Generic Plugin',
        'slug' => 'generic-plugin',
        'description' => 'This plugin is very similar to what you need',
        'active_installs' => 5000, // larger install count but less relevant
    ]);

    Plugin::factory()->create([
        'name' => 'Similar Plugin',
        'slug' => 'similar-plugin',
        'short_description' => 'A generic description',
        'active_installs' => 1000,
    ]);

    // Create the service
    $service = new QueryPluginsService();

    // Create a request with search for "similar"
    $request = new QueryPluginsRequest(
        search: 'similar',
        page: 1,
        per_page: 10,
    );

    // Query plugins
    $response = $service->queryPlugins($request);

    // The first plugin should be "Similar Plugin" because name similarity
    // has higher weight than description match
    expect($response->plugins)
        ->toHaveCount(2)
        ->and($response->plugins->first()->name)->toBe('Similar Plugin')
        ->and($response->plugins->last()->name)->toBe('Generic Plugin');
});

test('applySearchWeighted real world example #1', function () {
    Plugin::factory()->create([
        'name' => 'WooCommerce',
        'slug' => 'woocommerce',
        'short_description' => "Everything you need to launch an online store in days and keep it growing for years. From your first sale to millions in revenue, Woo is with you.",
        'description' => '<p><a href="https://woocommerce.com/woocommerce/" rel="nofollow ugc">WooCommerce</a> is the open-source ecommerce platform f…d how it is used, please refer to our <a href="https://automattic.com/privacy/" rel="nofollow ugc">Privacy Policy</a>.</p>\n',
        'active_installs' => 10000000, // the max .org reports
    ]);

    Plugin::factory()->create([
        'name' => 'LiteSpeed Cache',
        'slug' => 'litespeed-cache',
        'short_description' => "All-in-one unbeatable acceleration &amp; PageSpeed improvement: caching, image/CSS/JS optimization...",
        'active_installs' => 7000000,
    ]);

    // Create the service
    $service = new QueryPluginsService();

    // Create a request with search for "similar"
    $request = new QueryPluginsRequest(
        search: 'cache',
        page: 1,
        per_page: 10,
    );

    // Query plugins
    $response = $service->queryPlugins($request);

    expect($response->plugins)
        ->and($response->plugins->first()->name)->toBe('LiteSpeed Cache');
});

