<?php

namespace App\Services\PluginServices;

use App\Models\WpOrg\PluginTag;
use App\Values\WpOrg\PluginDTOs\PluginHotTagsResponse;

class PluginHotTagsService
{
    /**
     * Gets the top tags by plugin count
     *
     * @return array<string, array{ name: string, slug: string, count: int }>
     */
    public function getHotTags(int $count = -1): array
    {
        $hotTags = PluginTag::query()
            ->select('plugin_tags.*')
            ->join('plugin_plugin_tags', 'plugin_tags.id', '=', 'plugin_plugin_tags.plugin_tag_id')
            ->selectRaw('COUNT(plugin_plugin_tags.plugin_id) as plugins_count')
            ->groupBy('plugin_tags.id', 'plugin_tags.name', 'plugin_tags.slug')
            ->orderBy('plugins_count', 'desc')
            ->limit($count >= 0 ? $count : 100)
            ->get();

        // This was the first attempt, and it blows up the database with nested scans.  Do not do this.
        // $hotTags = PluginTag::query()
        //     ->withCount('plugins')
        //     ->orderBy('plugins_count', 'desc')
        //     ->limit($count >= 0 ? $count : 100)
        //     ->get();

        return PluginHotTagsResponse::collect($hotTags)
            ->mapWithKeys(fn(PluginHotTagsResponse $tag) => [$tag->slug => $tag])
            ->toArray();
    }
}
