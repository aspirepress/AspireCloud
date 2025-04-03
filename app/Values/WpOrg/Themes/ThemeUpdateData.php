<?php

namespace App\Values\WpOrg\Themes;

use App\Models\WpOrg\Theme;
use Bag\Attributes\Transforms;
use Bag\Bag;

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

    /** @return array<string, mixed> */
    #[Transforms(Theme::class)]
    public static function fromTheme(Theme $theme): array
    {
        return [
            'name' => $theme->name,
            'theme' => $theme->slug,
            'new_version' => $theme->version,
            'url' => $theme->download_link,
            'package' => $theme->download_link,
            'requires' => $theme->requires,
            'requires_php' => $theme->requires_php,
        ];
    }
}
