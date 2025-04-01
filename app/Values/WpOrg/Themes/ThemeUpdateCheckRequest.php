<?php

namespace App\Values\WpOrg\Themes;

use Bag\Attributes\Transforms;
use Bag\Bag;
use Illuminate\Http\Request;

use function Safe\json_decode;

/**
 * @phpstan-type TranslationMetadata array{
 *     POT-Creation-Date: string,
 *     PO-Revision-Date: string,
 *     Project-Id-Version: string,
 *     X-Generator: string
 * }
 */
readonly class ThemeUpdateCheckRequest extends Bag
{
    /**
     * @param string $active // Active theme slug
     * @param array<string, array{"Version": string}> $themes
     * @param array<string, array<string, TranslationMetadata>> $translations
     * @param list<string> $locale
     */
    public function __construct(
        public ?string $active = null, // text to search
        public ?array $themes = null,
        public ?array $translations = null,
        public ?array $locale = null,
    ) {}

    /** @return array<string, mixed> */
    #[Transforms(Request::class)]
    public static function fromRequest(Request $request): array
    {
        $themes = $request->post('themes');
        $locale = $request->post('locale');
        $translations = $request->post('translations');
        $themeData = json_decode($themes, true);
        return [
            'active' => $themeData['active'],
            'themes' => $themeData['themes'],
            'locale' => json_decode($locale, true),
            'translations' => json_decode($translations, true),
        ];
    }
}
