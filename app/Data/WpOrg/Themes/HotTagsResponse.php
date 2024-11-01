<?php

namespace App\Data\WpOrg\Themes;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class HotTagsResponse extends Data
{
    public function __construct(
        public string $slug,
        public string $name,
        public int $count,
    ) {}

    /**
     * Static method to create an instance from a Theme model.
     * @param Collection<int,covariant array{
     *   slug: string,
     *   name: string,
     *   count: int,
     * }> $themeTags
     * @return Collection<string, covariant HotTagsResponse>
     */
    public static function fromCollection(Collection $themeTags): Collection
    {
        return $themeTags->mapWithKeys(fn($theme) => [
            $theme['slug'] => self::from($theme),
        ]);
    }
}
