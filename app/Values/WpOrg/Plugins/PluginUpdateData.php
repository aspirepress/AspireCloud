<?php

namespace App\Values\WpOrg\Plugins;

use App\Models\WpOrg\Plugin;
use Bag\Attributes\Transforms;
use Bag\Bag;

readonly class PluginUpdateData extends Bag
{
    public function __construct(
        public string $name,
        public string $plugin,
        public string $new_version,
        public string $url,
        public string $package,
        public ?string $requires,
        public ?string $requires_php,
    ) {}

    /** @return array<string, mixed> */
    #[Transforms(Plugin::class)]
    public static function fromPlugin(Plugin $plugin): array
    {
        return [
            'name' => $plugin->name,
            'plugin' => $plugin->slug,
            'new_version' => $plugin->version,
            'url' => $plugin->download_link,
            'package' => $plugin->download_link,
            'requires' => $plugin->requires,
            'requires_php' => $plugin->requires_php,
        ];
    }
}
