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
        $hotTags = ThemeTag::withCount('themes') // Count associated themes for each tag
            ->orderBy('themes_count', 'desc') // Order by the count of themes in descending order
            ->limit($count >= 0 ? $count : 100) // Limit to the top 100 tags
            ->get(['slug', 'name', 'themes_count']) // Select only slug and themes_count
            ->map(function ($tag) {
                return [
                    'name' => (string) $tag->name, // Format name from slug
                    'slug' => (string) $tag->slug,
                    'count' => (int) $tag->themes_count, // Use themes_count from withCount
                ];
            });
        return HotTagsResponse::fromCollection($hotTags)->toArray();
    }
}
