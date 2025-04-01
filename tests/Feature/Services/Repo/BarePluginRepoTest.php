<?php

declare(strict_types=1);

use App\Services\Repo\BarePluginRepo;

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
    expect($plugin->ac_raw_metadata)->toBeEmpty();
    expect($plugin->download_link)->toBe('gopher://test');
    expect($plugin->repository_url)->toBe('wais://test');
});
