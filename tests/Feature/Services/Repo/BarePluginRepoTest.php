<?php

declare(strict_types=1);

use App\Services\Repo\BarePluginRepo;

test('Git Plugin Repo', function () {
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
        repository_url: 'wais://test',
    );
    $then = now()->subSeconds(10);
    $now = now();
    expect($plugin->ac_origin)->toBe('bare');
    expect($plugin->added)->toBeBetween($then, $now);
    expect($plugin->last_updated)->toBeBetween($then, $now);
    expect($plugin->ac_created)->toBeBetween($then, $now);
    expect($plugin->ac_raw_metadata)->toBeNull();
    expect($plugin->download_link)->toBe('gopher://test'); // invalid url becomes null
    expect($plugin->repository_url)->toBe('wais://test'); // invalid url becomes null
});
