<?php

namespace App\Values\WpOrg\Themes;

use Bag\Bag;
use Illuminate\Http\Request;

use function Safe\json_decode;

readonly class ThemeUpdateCheckTranslationCollection extends Bag
{
    /**
     * @param ?array<string,mixed> $themes
     * @param ?array<string,mixed> $translations
     * @param ?string[] $locale
     */
    public function __construct(
        public ?string $active = null, // text to search
        public ?array $themes = null,
        public ?array $translations = null,
        public ?array $locale = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $themes = $request->post('themes');
        $locale = $request->post('locale');
        $translations = $request->post('translations');
        $themeData = json_decode($themes, true);
        return static::from([
            'active' => $themeData['active'],
            'themes' => $themeData['themes'],
            'locale' => json_decode($locale, true),
            'translations' => json_decode($translations, true),
        ]);
    }
}
