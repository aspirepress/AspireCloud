<?php

namespace App\Services\Plugins;

use App\Data\WpOrg\Themes\ThemeHotTagsResponse;
use App\Models\WpOrg\PluginTag;

class PluginHotTagsService
{
    /**
     * Gets the top tags by plugin count
     *
     * @return array<string, array{
     *   name: string,
     *  slug: string,
     *  count: int,
     * }> */
    public function getHotTags(int $count = -1): array
    {
        $hotTags = PluginTag::withCount('plugins')
            ->orderBy('plugins_count', 'desc')
            ->limit($count >= 0 ? $count : 100)
            ->get(['slug', 'name', 'plugins_count'])
            ->map(function ($tag) {
                return [
                    'name' => (string) $tag->name,
                    'slug' => (string) $tag->slug,
                    'count' => (int) $tag->plugins_count,
                ];
            });
        return ThemeHotTagsResponse::fromCollection($hotTags)->toArray();
    }
}
