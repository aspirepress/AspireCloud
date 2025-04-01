<?php

namespace App\Values\WpOrg\Plugins;

use Bag\Attributes\Transforms;
use Bag\Bag;
use Illuminate\Http\Request;

use function Safe\json_decode;

readonly class PluginUpdateCheckRequest extends Bag
{
    /**
     * @phpstan-type TranslationMetadata array{
     *     POT-Creation-Date: string,                // Creation date of the POT file
     *     PO-Revision-Date: string,                 // Revision date of the PO file
     *     Project-Id-Version: string,               // Project version info
     *     X-Generator: string                       // Generator software info
     * }
     *
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
