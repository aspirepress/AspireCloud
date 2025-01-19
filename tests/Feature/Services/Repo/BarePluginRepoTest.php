<?php

declare(strict_types=1);

use App\Services\Repo\BarePluginRepo;
use Illuminate\Validation\ValidationException;

test('BarePluginRepo basics', function () {
    $repo = new BarePluginRepo();
    $plugin = $repo->createPlugin(
        slug: 'test',
        name: 'test',
        short_description: 'test',
        description: 'test',
        version: 'test',
        author: 'test',
        requires: 'test',
        tested: 'test',
        download_link: 'gopher://test',
        extra: [
            'repository_url' => 'wais://test',
        ],
    );
    $then = now()->subSeconds(10);
    $now = now();
    expect($plugin->ac_origin)->toBe('bare');
    expect($plugin->added)->toBeBetween($then, $now);
    expect($plugin->last_updated)->toBeBetween($then, $now);
    expect($plugin->ac_created)->toBeBetween($then, $now);
    expect($plugin->ac_raw_metadata)->toBeNull();
    expect($plugin->download_link)->toBe('gopher://test');
    expect($plugin->repository_url)->toBe('wais://test');
});

// not a comprehensive test of validators here, we're just proving that validation happens at all
test('BarePluginRepo validates arguments', function () {
    $repo = new BarePluginRepo();
    $repo->createPlugin(
        slug: 'test',
        name: 'test',
        short_description: 'test',
        description: 'test',
        version: 'test',
        author: 'test',
        requires: 'test',
        tested: 'test',
        download_link: 'invalid',
        extra: [
            'repository_url' => 'wais://test',
        ],
    );
})->throws(ValidationException::class);
