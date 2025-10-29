<?php
declare(strict_types=1);

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
            ->select('theme_tags.*')
            ->join('theme_theme_tags', 'theme_tags.id', '=', 'theme_theme_tags.theme_tag_id')
            ->selectRaw('COUNT(theme_theme_tags.theme_id) as themes_count')
            ->groupBy('theme_tags.id', 'theme_tags.name', 'theme_tags.slug')
            ->orderBy('themes_count', 'desc')
            ->limit($count >= 0 ? $count : 100)
            ->get();

        // this blows up the database.  do not use this.
        // $hotTags = ThemeTag::query()
        //     ->withCount('themes')
        //     ->orderBy('themes_count', 'desc')
        //     ->limit($count >= 0 ? $count : 100)
        //     ->get();

        return ThemeHotTagsResponse::collect($hotTags)
            ->mapWithKeys(fn(ThemeHotTagsResponse $tag) => [$tag->slug => $tag])
            ->toArray();
    }
}
