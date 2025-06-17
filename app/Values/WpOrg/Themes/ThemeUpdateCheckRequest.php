<?php

namespace App\Values\WpOrg\Themes;

use App\Values\DTO;
use Bag\Attributes\Transforms;
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
readonly class ThemeUpdateCheckRequest extends DTO
{
    /**
     * @param string $active slug of currently active theme
     * @param array<string, array{"Version": string}> $themes
     * @param array<string, array<string, TranslationMetadata>> $translations
     * @param list<string> $locale
     */
    public function __construct(
        public string $active,
        public array $themes,
        public array $translations,
        public array $locale,
    ) {}

    /** @return array<string, mixed> */
    #[Transforms(Request::class)]
    public static function fromRequest(Request $request): array
    {
        $decode = fn($key) => json_decode($request->post($key), true);
        $themes = $decode('themes');
        return [
            'active' => $themes['active'],
            'themes' => $themes['themes'],
            'locale' => $decode('locale'),
            'translations' => $decode('translations'),
        ];
    }
}
