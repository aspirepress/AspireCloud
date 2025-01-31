<?php

namespace App\Services\Plugins;

use App\Models\WpOrg\Plugin;
use Illuminate\Support\Collection;

class PluginUpdateService
{
    /**
     * Process the plugins and check for updates
     *
     * @param array<string, array{Version?: string}> $plugins
     * @return array{
     *     updates: Collection<string, array<string, mixed>>,
     *     no_updates: Collection<string, array<string, mixed>>
     * }
     */
    public function processPlugins(array $plugins, bool $includeAll): array
    {
        $updates = collect();
        $noUpdates = collect();

        foreach ($plugins as $pluginFile => $pluginData) {
            $plugin = $this->findPlugin($pluginFile, $pluginData);

            if (!$plugin) {
                continue;
            }

            $updateData = $this->formatPluginData($plugin, $pluginFile);

            if (version_compare($plugin->version, $pluginData['Version'] ?? '', '>')) {
                $updates->put($pluginFile, $updateData);
            } elseif ($includeAll) {
                // Only collect no_updates when includeAll is true
                $noUpdates->put($pluginFile, $updateData);
            }
        }

        return [
            'updates' => $updates,
            'no_updates' => $noUpdates,
        ];
    }

    /**
     * Find a plugin by its file path and data
     *
     * @param array{Version?: string} $pluginData
     */
    private function findPlugin(string $pluginFile, array $pluginData): ?Plugin
    {
        $slug = $this->extractSlug($pluginFile);
        if (!$slug || empty($pluginData['Version'])) {
            return null;
        }

        return Plugin::query()
            ->where('slug', $slug)
            ->first();
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
            'id' => "w.org/plugins/{$plugin->slug}",
            'slug' => $plugin->slug,
            'plugin' => $pluginFile,
            'new_version' => $plugin->version,
            'url' => "https://wordpress.org/plugins/{$plugin->slug}/",
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
