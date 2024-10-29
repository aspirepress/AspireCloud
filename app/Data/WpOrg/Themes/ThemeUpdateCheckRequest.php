<?php

namespace App\Data\WpOrg\Themes;

use Illuminate\Http\Request;
use Spatie\LaravelData\Data;

use function Safe\json_decode;

class ThemeUpdateCheckRequest extends Data
{
    /**
     * @phpstan-type TranslationMetadata array{
     *     POT-Creation-Date: string,                // Creation date of the POT file
     *     PO-Revision-Date: string,                 // Revision date of the PO file
     *     Project-Id-Version: string,               // Project version info
     *     X-Generator: string                       // Generator software info
     * }
     */

    /**
        * @param string $active                    // Active theme slug
        * @param array<string,array{
        *     "Version": string,
        * }> $themes                               // Array of theme slugs and their current versions
        * @param array<string,array<string,array{
     *     POT-Creation-Date: string,
     *     PO-Revision-Date: string,
     *     Project-Id-Version: string,
     *     X-Generator: string
     * }>> $translations
        * @param string[] $locale             // Array of locale strings
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
