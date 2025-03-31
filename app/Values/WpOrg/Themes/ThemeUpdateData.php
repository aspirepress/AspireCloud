<?php

namespace App\Values\WpOrg\Themes;

use App\Models\WpOrg\Theme;
use Bag\Bag;
use Illuminate\Support\Collection;

readonly class ThemeUpdateData extends Bag
{
    public function __construct(
        public string $name,
        public string $theme,
        public string $new_version,
        public string $url,
        public string $package,
        public ?string $requires,
        public ?string $requires_php,
    ) {}

    public static function fromModel(Theme $theme): self
    {
        return new self(
            name: $theme->name,
            theme: $theme->slug,
            new_version: $theme->version,
            url: $theme->download_link,
            package: $theme->download_link,
            requires: $theme->requires,
            requires_php: $theme->requires_php,
        );
    }

    /**
     * @param Collection<int,Theme> $themes
     * @return Collection<string,ThemeUpdateData>
     */
    public static function fromModelCollection(Collection $themes): Collection
    {
        return $themes->mapWithKeys(fn($theme) => [$theme->slug => self::fromModel($theme)]);
    }
}
