<?php

namespace App\Values\WpOrg\Plugins;

use App\Models\WpOrg\Plugin;
use Bag\Bag;
use Illuminate\Support\Collection;

readonly class PluginUpdateCheckResponse extends Bag
{
    /**
     * @param Collection<string, PluginUpdateData> $plugins
     * @param Collection<string, PluginUpdateData> $no_update
     * @param Collection<array-key, mixed> $translations
     */
    public function __construct(
        public Collection $plugins,
        public Collection $no_update,
        public Collection $translations,
    ) {}

    /**
     * @param iterable<array-key, Plugin> $plugins
     * @param iterable<array-key, Plugin> $no_update
     */
    public static function fromResults(iterable $plugins, iterable $no_update): self
    {
        return new self(
            plugins: PluginUpdateData::collect($plugins)->keyBy('plugin'),
            no_update: PluginUpdateData::collect($no_update)->keyBy('plugin'),
            translations: collect(), // TODO
        );
    }
}
