<?php

namespace App\Data\WpOrg\Themes;

use App\Models\WpOrg\Theme;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataProperty;
use Spatie\LaravelData\Data;

class ThemeUpdateData extends Data
{
    public function __construct(
        public string $name,
        public string $theme,
        public string $new_version,
        public string $url,
        public string $package,
        public ?string $requires,
        public string $requires_php,
    ) {}

    /**
    * Static method to create an instance from a Theme model.
    * @param Theme $theme
    * @return ThemeUpdateData
    */
    public static function fromModel(Theme $theme): self
    {
        return new self(
            name: $theme->name,
            theme: $theme->slug,
            new_version: $theme->version,
            url: $theme->download_link,
            package: "downloadurl_placeholder{$theme->version}",
            requires: $theme->requires['wordpress'] ?? null,
            requires_php: $theme->requires_php,
        );
    }

    /**
    * Static method to create an instance from a Theme model.
    * @param Collection<int,Theme> $themes
    * @return Collection<string,ThemeUpdateData>
    */
    public static function fromModelCollection(Collection $themes): Collection
    {
        return $themes->mapWithKeys(fn($theme) => [
            $theme->slug => self::fromModel($theme),
        ]);
    }
}
