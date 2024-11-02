<?php

namespace App\Services\Themes;

use App\Data\WpOrg\Themes\HotTagsResponse;
use App\Models\WpOrg\ThemeTag;

class HotTagsService
{
    /**
     * Gets the top tags by theme count
     *
     * @return array<string, array{
     *   name: string,
     *  slug: string,
     *  count: int,
     * }> */
    public function getHotTags(int $count = -1): array
    {
        $hotTags = ThemeTag::withCount('themes')
            ->orderBy('themes_count', 'desc')
            ->limit($count >= 0 ? $count : 100)
            ->get(['slug', 'name', 'themes_count'])
            ->map(function ($tag) {
                return [
                    'name' => (string) $tag->name,
                    'slug' => (string) $tag->slug,
                    'count' => (int) $tag->themes_count,
                ];
            });
        return HotTagsResponse::fromCollection($hotTags)->toArray();
    }
}
