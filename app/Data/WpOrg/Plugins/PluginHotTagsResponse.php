<?php

namespace App\Data\WpOrg\Plugins;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class PluginHotTagsResponse extends Data
{
    public function __construct(
        public string $slug,
        public string $name,
        public int $count,
    ) {}

    /**
     * Static method to create an instance from a Plugin model.
     * @param Collection<int,covariant array{
     *   slug: string,
     *   name: string,
     *   count: int,
     * }> $pluginTags
     * @return Collection<string, covariant PluginHotTagsResponse>
     */
    public static function fromCollection(Collection $pluginTags): Collection
    {
        return $pluginTags->mapWithKeys(fn($plugin) => [
            $plugin['slug'] => self::from($plugin),
        ]);
    }
}
