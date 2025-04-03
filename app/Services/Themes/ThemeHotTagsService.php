<?php

namespace App\Services\Themes;

use App\Models\WpOrg\ThemeTag;
use App\Values\WpOrg\Themes\ThemeHotTagsResponse;

class ThemeHotTagsService
{
    /**
     * Gets the top tags by theme count
     *
     * @return array<string, array{name: string, slug: string, count: int}>
     */
    public function getHotTags(int $count = -1): array
    {
        $hotTags = ThemeTag::query()
            ->withCount('themes')
            ->orderBy('themes_count', 'desc')
            ->limit($count >= 0 ? $count : 100)
            ->get();

        return ThemeHotTagsResponse::collect($hotTags)
            ->mapWithKeys(fn(ThemeHotTagsResponse $tag) => [$tag->slug => $tag])
            ->toArray();
    }
}
