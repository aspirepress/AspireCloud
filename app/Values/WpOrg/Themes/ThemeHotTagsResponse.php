<?php

namespace App\Values\WpOrg\Themes;

use App\Models\WpOrg\ThemeTag;
use Bag\Attributes\Transforms;
use Bag\Bag;

readonly class ThemeHotTagsResponse extends Bag
{
    public function __construct(
        public string $slug,
        public string $name,
        public int $count,
    ) {}

    /** @return array{slug: string, name: string, count: int} */
    #[Transforms(ThemeTag::class)]
    public static function fromThemeTag(ThemeTag $tag): array
    {
        return ['slug' => $tag->slug, 'name' => $tag->name, 'count' => $tag->themes_count];
    }
}
