<?php

namespace App\Values\WpOrg\Plugins;

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
readonly class PluginUpdateCheckRequest extends DTO
{
    /**
     * @param array<string, array{"Version": string}> $plugins
     * @param array<string, array<string, TranslationMetadata>> $translations
     * @param list<string> $locale
     */
    public function __construct(
        public array $plugins,
        public array $translations,
        public array $locale,
        public bool $all = false,
    ) {}

    /** @return array<string, mixed> */
    #[Transforms(Request::class)]
    public static function fromRequest(Request $request): array
    {
        $decode = fn($key) => json_decode($request->post($key), true);
        return [
            'plugins' => $decode('plugins')['plugins'],
            'locale' => $decode('locale'),
            'translations' => $decode('translations'),
            'all' => $request->boolean('all'),
        ];
    }
}
