<?php

namespace App\Values\WpOrg\Plugins;

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
readonly class PluginUpdateCheckRequest extends Bag
{
    /**
     * @param array<string, array{"Version": string}> $plugins
     * @param array<string, array<string, TranslationMetadata>> $translations
     * @param list<string> $locale
     */
    public function __construct(
        public ?array $plugins = null,
        public ?array $translations = null,
        public ?array $locale = null,
        public bool $all = false,
    ) {}

    /** @return array<string, mixed> */
    #[Transforms(Request::class)]
    public static function fromRequest(Request $request): array
    {
        $plugins = $request->post('plugins');
        $locale = $request->post('locale');
        $translations = $request->post('translations');
        $pluginData = json_decode($plugins, true);
        $all = $request->boolean('all');
        return [
            'plugins' => $pluginData['plugins'],
            'locale' => json_decode($locale, true),
            'translations' => json_decode($translations, true),
            'all' => $all,
        ];
    }
}
