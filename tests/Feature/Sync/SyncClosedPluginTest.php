<?php

declare(strict_types=1);

use App\Models\WpOrg\ClosedPlugin;
use App\Models\WpOrg\Plugin;

describe('Sync Closed Plugins', function () {
    $md_0gravatar =  [
        "slug" => "0gravatar",
        "name" => "Display Name If No Gravatar",
        "status" => "closed",
        "error" => "closed",
        "description" => "This plugin has been closed as of November 13, 2024 and is not available for download. This closure is permanent. Reason: Author Request.",
        "closed" => true,
        "closed_date" => "2024-11-13",
        "reason" => "author-request",
        "reason_text" => "Author Request",
        "aspiresync_meta" => [
            "id" => "01933d0c-12a4-72a3-a042-52cd65f40126",
            "type" => "plugin",
            "slug" => "0gravatar",
            "name" => "Display Name If No Gravatar",
            "status" => "closed",
            "version" => null,
            "origin" => "wp_org",
            "updated" => "2024-11-13",
            "pulled" => "2024-11-18T02:13:41+00:00",
        ],
    ];

    it('loads as ClosedPlugin', function () use ($md_0gravatar) {
        $plugin = ClosedPlugin::fromSyncMetadata($md_0gravatar);
        expect($plugin)
            ->toBeInstanceOf(ClosedPlugin::class)
            ->and($plugin->slug)->toBe('0gravatar')
            ->and($plugin->name)->toBe('Display Name If No Gravatar')
            ->and($plugin->description)->toBe('This plugin has been closed as of November 13, 2024 and is not available for download. This closure is permanent. Reason: Author Request.')
            ->and($plugin->closed_date)->toBeBetween(new DateTime('2024-11-13'), new DateTime('2024-11-14'))
            ->and($plugin->reason)->toBe('author-request')
            ->and($plugin->ac_shadow_id)->toBeNull()
            ->and($plugin->ac_origin)->toBe('wp_org');
    });

    it('throws an exception if loaded as Plugin', function () use ($md_0gravatar) {
        expect(function () use ($md_0gravatar) {
            Plugin::fromSyncMetadata($md_0gravatar);
        })->toThrow(InvalidArgumentException::class);
    });
});
