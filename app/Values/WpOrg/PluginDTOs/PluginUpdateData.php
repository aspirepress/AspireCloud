<?php

namespace App\Values\WpOrg\PluginDTOs;

use App\Models\WpOrg\Plugin;
use App\Values\DTO;
use Bag\Attributes\Transforms;
use Bag\Values\Optional;

readonly class PluginUpdateData extends DTO
{
    /**
     * @param Optional|list<string> $requires_plugins
     * @param Optional|array<string, mixed> $compatibility
     * @param Optional|array<string, mixed> $icons
     * @param Optional|array<string, mixed> $banners
     * @param Optional|array<string, mixed> $banners_rtl
     */
    public function __construct(
        public string $id,
        public string $slug,
        public string $plugin,
        public string $url,
        public string $package,
        public string|null $requires,
        public string|null $tested,
        public string|null $requires_php,
        public Optional|array $requires_plugins,
        public Optional|array $compatibility,
        public Optional|array $icons,
        public Optional|array $banners,
        public Optional|array $banners_rtl,
        public Optional|string $new_version,
        public Optional|string $upgrade_notice,
    ) {}

    /** @return array<string, mixed> */
    #[Transforms(Plugin::class)]
    public static function fromPlugin(Plugin $plugin): array
    {
        $slug = $plugin->slug;
        return [
            'id' => "w.org/plugins/$slug",
            'slug' => $slug,
            'plugin' => "$slug/$slug.php", // gets rewritten to the "real" filename later. hacky, but it works for this.
            'new_version' => $plugin->version,
            'url' => "https://wordpress.org/plugins/$slug/",
            'package' => $plugin->download_link,
            'icons' => $plugin->icons,
            'banners' => $plugin->banners,
            'banners_rtl' => [],
            'requires' => $plugin->requires,
            'tested' => $plugin->tested,
            'requires_php' => $plugin->requires_php,
            'requires_plugins' => $plugin->requires_plugins,
            'compatibility' => $plugin->compatibility,
            // TODO: upgrade_notice (maybe in metadata somewhere?)
        ];
    }
}
