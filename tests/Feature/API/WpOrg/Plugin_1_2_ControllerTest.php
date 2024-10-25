<?php

use App\Models\WpOrg\Plugin;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Run migrations
    $this->artisan('migrate');
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

it('returns plugin information in wordpress.org format', function () {
    $plugin = Plugin::factory()->create([
        'name' => 'JWT Authentication for WP REST API',
        'slug' => 'jwt-authentication-for-wp-rest-api',
    ]);

    $response = $this->getJson('/plugins/info/1.2?action=plugin_information&slug=jwt-authentication-for-wp-rest-api');

    $response->assertStatus(200)
        ->assertJsonStructure([
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
        ])
        ->assertJson([
            'name' => 'JWT Authentication for WP REST API',
        ]);
});
