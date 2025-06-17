<?php

namespace App\Values\WpOrg\Themes;

use App\Models\WpOrg\Theme;
use App\Values\DTO;
use Bag\Attributes\Transforms;

readonly class ThemeUpdateData extends DTO
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
