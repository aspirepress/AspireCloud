<?php

namespace App\Services\PluginServices;

use App\Models\WpOrg\Plugin;
use App\Values\WpOrg\PluginDTOs\PluginUpdateCheckRequest;
use App\Values\WpOrg\PluginDTOs\PluginUpdateCheckResponse;
use App\Values\WpOrg\PluginDTOs\PluginUpdateData;

class PluginUpdateService
{
    public function checkForUpdates(PluginUpdateCheckRequest $req): PluginUpdateCheckResponse
    {
        $bySlug = collect($req->plugins)
            ->mapWithKeys(
                fn($pluginData, $pluginFile) => [$this->extractSlug($pluginFile) => [$pluginFile, $pluginData]],
            );

        $isUpdated = fn($plugin) => version_compare($plugin->version, $bySlug[$plugin->slug][1]['Version'] ?? '', '>');

        $mkUpdate = function ($plugin) use ($bySlug) {
            $file = $bySlug[$plugin->slug][0];
            return [$file => PluginUpdateData::from($plugin)->with(plugin: $file)];
        };

        [$updates, $no_updates] = Plugin::query()
            ->whereIn('slug', $bySlug->keys())
            ->get()
            ->partition($isUpdated)
            ->map(fn($collection) => $collection->mapWithKeys($mkUpdate));

        return PluginUpdateCheckResponse::from(plugins: $updates, no_update: $no_updates, translations: collect([]));
    }

    /**
     * Extract the plugin slug from the plugin file path
     */
    private function extractSlug(string $pluginFile): string
    {
        return str_contains($pluginFile, '/')
            ? explode('/', $pluginFile)[0]
            : pathinfo($pluginFile, PATHINFO_FILENAME);
    }
}
