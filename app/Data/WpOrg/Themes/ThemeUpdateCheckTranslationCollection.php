<?php

namespace App\Data\WpOrg\Themes;

use Illuminate\Http\Request;
use Spatie\LaravelData\Data;

use function Safe\json_decode;

class ThemeUpdateCheckTranslationCollection extends Data
{
    /**

        * @param ?string $active
        * @param ?array<string,mixed> $themes
        * @param ?array<string,mixed> $translations
        * @param ?string[] $locale
     */
    public function __construct(
        public readonly ?string $active = null, // text to search
        public readonly ?array $themes = null,
        public readonly ?array $translations = null,
        public readonly ?array $locale = null,
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
