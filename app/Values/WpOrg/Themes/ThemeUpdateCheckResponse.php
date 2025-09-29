<?php

namespace App\Values\WpOrg\Themes;

use App\Models\WpOrg\Theme;
use App\Values\DTO;
use Illuminate\Support\Collection;

readonly class ThemeUpdateCheckResponse extends DTO
{
    /**
     * @param Collection<string, ThemeUpdateData> $themes
     * @param Collection<string, ThemeUpdateData> $no_update
     * @param Collection<array-key, mixed> $translations
     */
    public function __construct(
        public Collection $themes,
        public Collection $no_update,
        public Collection $translations,
    ) {}

    /**
     * @param iterable<array-key, Theme> $themes
     * @param iterable<array-key, Theme> $no_update
     */
    public static function fromResults(iterable $themes, iterable $no_update): self
    {
        $mkUpdates = fn(iterable $items) => ThemeUpdateData::collect($items)->keyBy('theme');

        return new self(
            themes: $mkUpdates($themes),
            no_update: $mkUpdates($no_update),
            translations: collect(), // TODO
        );
    }
}
