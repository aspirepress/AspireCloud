<?php

namespace App\Services\Plugins;

use App\Models\WpOrg\Plugin;
use App\Values\WpOrg\Plugins\PluginUpdateCheckRequest;
use Illuminate\Support\Collection;

class PluginUpdateService
{
    /**
     * @return array{
     *     updates: Collection<string, array<string, mixed>>,
     *     no_updates: Collection<string, array<string, mixed>>
     * }
     */
    public function checkForUpdates(PluginUpdateCheckRequest $req): array
    {
        $bySlug = collect($req->plugins)
            ->mapWithKeys(
                fn($pluginData, $pluginFile) => [$this->extractSlug($pluginFile) => [$pluginFile, $pluginData]],
            );

        $isUpdated = fn($plugin) => version_compare($plugin->version, $bySlug[$plugin->slug][1]['Version'] ?? '', '>');

        $mkUpdate = function ($plugin) use ($bySlug) {
            $file = $bySlug[$plugin->slug][0];
            return [$file => $this->formatPluginData($plugin, $file)];
        };

        [$updates, $no_updates] = Plugin::query()
            ->whereIn('slug', $bySlug->keys())
            ->get()
            ->partition($isUpdated)
            ->map(fn($collection) => $collection->mapWithKeys($mkUpdate));

        return compact('updates', 'no_updates');
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

    /**
     * Format plugin data for the response
     *
     * @return array<string, mixed>
     */
    private function formatPluginData(Plugin $plugin, string $pluginFile): array
    {
        return [
            'id' => "w.org/plugins/$plugin->slug",
            'slug' => $plugin->slug,
            'plugin' => $pluginFile,
            'new_version' => $plugin->version,
            'url' => "https://wordpress.org/plugins/$plugin->slug/",
            'package' => $plugin->download_link,
            'icons' => $plugin->icons,
            'banners' => $plugin->banners,
            'banners_rtl' => [],
            'requires' => $plugin->requires,
            'tested' => $plugin->tested,
            'requires_php' => $plugin->requires_php,
            'requires_plugins' => $plugin->requires_plugins,
            'compatibility' => $plugin->compatibility,
        ];
    }
}
